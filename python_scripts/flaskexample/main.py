from flask import Flask, request
import mysql.connector
import os
import cv2
import numpy as np
import traceback

subjects = [""] #The first subject at index 0 is not mapped to any userid (cos there is no user id 0) 
subjectsUserIdDict = dict()
subjectsUserIdDict[0]=-1
#create our LBPH face recognizer 
face_recognizer = cv2.face.LBPHFaceRecognizer_create()

#Setup connection to MariaDB
db = mysql.connector.connect(host="localhost", user="root", password="", database="db_facepay")

#Get cursor obj
cursor = db.cursor()

#Define sql statemets that will be in use
sql_get_user = "select id, username, cardName from tbl_user where id=%s"
sql_get_training_files_for_user="select userId, imageName from tbl_user_images where userId=%s"
sql_get_test_files_for_user="select id, userId, imageName from tbl_user_image_auth_reqs where userId=%s"
sql_get_test_files_by_id="select id, userId, imageName from tbl_user_image_auth_reqs where id=%s"

app = Flask(__name__)

@app.route("/")
def index():
    return """<h1>Face Recognition using Local Binary Patterns (LBP) and OpenCV</h1><br> 
    <ul><li><a href='auth/user/1/dd'>Face recognition endpoint</a></li><br><li><a href='user/1'>User detail retrieval endpoint</a></li></ul>"""

@app.route('/user/<int:user_id>')
def show_user(user_id):
    cursor.execute(sql_get_user, (user_id, ))
    result = cursor.fetchone()
    if not result is None:
        jsonVal="{\"user_id\"" +":\"" +str(result[0])+"\",\"username\":\"" +str(result[1]) + "\", \"cardName\":\"" + str(result[2])+"\"}"
    else:
        jsonVal="{\"user_id\"" +":\"" +str(user_id)+"\",\"username\":\"\", \"cardName\":\"\"}"
    #% str(result[0]) % str(result[1]) % str(result[2])
    return jsonVal


#@app.route("/auth/user/<int:user_id>/<string:training_path>")
#def authenticate(user_id, training_path):
#   training_path = urllib.parse.unquote_plus(training_path)
@app.route("/auth/user/<int:user_id>")
def authenticate(user_id):
    #declare the variables 'subjects' and 'subjectsUserIdDict' and 'face_recognizer' as global variables
    global subjects
    global subjectsUserIdDict 
    global face_recognizer

    #clear the subjects list and the subjectsUserIdDict dictionary
    subjects.clear()
    subjectsUserIdDict.clear()

    training_path = ""
    prediction_status=0
    label_user_id=""
    cardName=""    
    user_auth_file_name=""
    error_message=""
    try:
        #there is no label 0 in our training data so subject name for index/label 0 is empty  
       
        subjects, subjectsUserIdDict = get_subjects_array_and_dict() 
        writeLog("authenticate( ):>> subjects len is " + str(len(subjects)) + "\n Dictionaries len is " + str(len(subjectsUserIdDict)) + "\n")
        #Print contents of the subjects
        for s in subjects:
            writeLog("\tauthenticate():>> subject content is "+s +"\n")

        #print the contents of the subjectsUserIdDict
        for key, value in subjectsUserIdDict.items():
            writeLog("\tauthenticate():>> dictionary content is KEY: "+ str(key) +", VALUE: "+ str(value)+"\n")
        
        #if "training_path" is empty
        if training_path == "":
            training_path = getGlobalTrainingPath()

        faces, labels = prepare_training_data(training_path)   
        #train our face recognizer of our training faces
        face_recognizer.train(faces, np.array(labels))
        user_auth_file_name = "test-data/test1.jpg"        
        user_auth_file_name = getUserAuthFile(user_id)

        if user_auth_file_name != "":
            test_img1 = cv2.imread(user_auth_file_name)
            #perform a prediction
            label_user_id, cardName = predict(test_img1)
            if label_user_id !='' :
                if int(label_user_id) >= 0:
                    prediction_status = 1
                else:
                    prediction_status = 0                     
            else:
                prediction_status = 0 
        else:
            prediction_status=0
            label_user_id=""
            cardName=""

    except Exception as err:
        prediction_status=0
        error_message = str(err) 
        fp=open("C:/xampp/htdocs/facepay\python_scripts/errorlog/log.txt","a")
        traceback.print_exc(limit=1, file=fp)
        fp.close()
    
    return '{\n"PredictionStatus":"'+str(prediction_status) + '",\n"RequestUserId":"' + str(user_id) + '",\n"PredictedUserId":"' + str(label_user_id) + '",\n"PredictedCardName":"' + str(cardName) + '",\n"InputImageFilename":"' + str(user_auth_file_name) + '",\n"ErrorMessage":"' + str(error_message)+ '"\n}'

@app.route("/image/boundingbox/<int:user_id>")
def drawBoundingBoxOnUserFace(user_id):
    imgFile=getUserAuthFile(user_id)
    newfilename = imgFile
    status="true"
    print("DEBUG>> Retrieved filename is ", imgFile)
    jsonUserDets = show_user(user_id)
    import json #imprt json package
    jsonObj = json.loads(jsonUserDets)
    label_text = jsonObj["cardName"]
    img = cv2.imread(imgFile)
    try:
        face, rect = detect_face(img)
        #draw a rectangle around face detected
        draw_rectangle(img, rect)
        #draw name of predicted person
        draw_text(img, label_text, rect[0], rect[1]-5)
        #write the image back to the another file name with an underscore
        filen, ext = os.path.splitext(imgFile)
        newfilename = filen + "_" + ext
        cv2.imwrite(newfilename, img)
    except Exception as e:
        print(e)
        status="false"

    relativepath = "../all_upload/testdata/" + getImmediateDirectoryName(newfilename) + "/" + os.path.basename(newfilename)
    return "{\"status\":\"" +status + "\",\"filename\":\"" + relativepath + "\"}"

@app.route("/testfp")
def testfp():
    fn="C:/xampp/htdocs/facepay/all_upload/testdata/s2/2.jpg"
    return getImmediateDirectoryName(fn)

def getImmediateDirectoryName(filename):
    list_folders = os.path.dirname(filename).split("/")
    lastIndex = len(list_folders)-1     
    return list_folders[lastIndex]

def getUserAuthFile(user_id):
    #sql="select id, userId, imageName from tbl_user_image_auth_reqs where userId=%s where id=MAX(id) LIMIT 1"
    sql="select id, userId, imageName, max(id) from tbl_user_image_auth_reqs where userId=%s group by id, userId, imageName having MAX(id)=id  order by MAX(id) desc  limit 1"
    value=(str(user_id), )
    cursor.execute(sql, value)
    result = cursor.fetchone()
    return str(result[2])



def getGlobalTrainingPath():
    cursor.execute("select valCol from tbl_settings where keyCol='training_path' LIMIT 1")
    result = cursor.fetchone()
    return str(result[0])

#Return all subjects in an array according to their userid
def get_subjects_array_and_dict():  
    count=1
    maxIndex=0
    cursor.execute("select max(id)+1 from tbl_user")
    result1 = cursor.fetchone()
    maxIndex = result1[0]
    cursor.execute("select id, cardName from tbl_user order by id asc")
    result= cursor.fetchall()
    global subjects 
    global subjectsUserIdDict

    for index in range(1, maxIndex):
        try:
            subjects.append(result[index - 1][1])
            subjectsUserIdDict[count] = str(result[index - 1][0])
            count = count + 1
        except:
            subjects.append("")
            subjectsUserIdDict[count] = ""
            count = count + 1
    

    # for ids in result:
    #     subjects.append(str(ids[1]))
    #     subjectsUserIdDict[index] = str(ids[0])
    #     index = index + 1
    
    return subjects, subjectsUserIdDict



#function to detect face using OpenCV
def detect_face(img):
    try:
        #convert the test image to gray image as opencv face detector expects gray images
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        #load OpenCV face detector, I am using LBP which is fast
        #there is also a more accurate but slow Haar classifier
        face_cascade = cv2.CascadeClassifier('opencv-files/lbpcascade_frontalface.xml')

        #let's detect multiscale (some images may be closer to camera than others) images
        #result is a list of faces
        faces = face_cascade.detectMultiScale(gray, scaleFactor=1.2, minNeighbors=5)
    
        num_faces_detected = len(faces)
        writeLog("detect_face( ) >> Number of faces is " + str(num_faces_detected) +"\n")        
        #if no faces are detected then return original img
        if (num_faces_detected == 0):            
            return None, None

        
    
        #under the assumption that there will be only one face,
        #extract the face area
        (x, y, w, h) = faces[0]
    
        
    except Exception as e:
        fp=open("C:/xampp/htdocs/facepay\python_scripts/errorlog/log.txt","a")
        traceback.print_exc(limit=1, file=fp)
    
    #return only the face part of the image
    return gray[y:y+w, x:x+h], faces[0]


#this function will read all persons' training images, detect face from each image
#and will return two lists of exactly same size, one list 
# of faces and another list of labels for each face
def prepare_training_data(data_folder_path):
    #list to hold all subject faces
    faces = []
    #list to hold labels for all subjects
    labels = []
    try:
        #------STEP-1--------
        #get the directories (one directory for each subject) in data folder
        dirs = os.listdir(data_folder_path)    
        #let's go through each directory and read images within it
        for dir_name in dirs:
        
            #our subject directories start with letter 's' so
            #ignore any non-relevant directories if any
            if not dir_name.startswith("s"):
                continue
            
            #------STEP-2--------
            #extract label number of subject from dir_name
            #format of dir name = slabel
            #, so removing letter 's' from dir_name will give us label
            label = int(dir_name.replace("s", ""))
        
            #build path of directory containin images for current subject subject
            #sample subject_dir_path = "training-data/s1"
            subject_dir_path = data_folder_path + "/" + dir_name
        
            #get the images names that are inside the given subject directory
            subject_images_names = os.listdir(subject_dir_path)
        
            #------STEP-3--------
            #go through each image name, read image, 
            #detect face and add face to list of faces
            for image_name in subject_images_names:
            
                #ignore system files like .DS_Store
                if image_name.startswith("."):
                    continue
            
                #build image path
                #sample image path = training-data/s1/1.pgm
                image_path = subject_dir_path + "/" + image_name

                #read image
                image = cv2.imread(image_path)
            
            
                #detect face
                face, rect = detect_face(image)
            
                #------STEP-4--------
                #for the purpose of this tutorial
                #we will ignore faces that are not detected
                if face is not None:
                    #add face to list of faces
                    faces.append(face)
                    #add label for this face
                    labels.append(label)
            
    except Exception as e:
        fp=open("C:/xampp/htdocs/facepay/python_scripts/errorlog/log.txt","a")
        traceback.print_exc(1, fp)

    return faces, labels




# **Did you notice** that instead of passing `labels` vector directly to face recognizer I am first converting it to **numpy** array? 
# This is because OpenCV expects labels vector to be a `numpy` array. 
# 
# Still not satisfied? Want to see some action? Next step is the real action, I promise! 

# ### Prediction

# Now comes my favorite part, the prediction part. This is where we actually get to see if our algorithm is actually recognizing our 
# trained subjects's faces or not. We will take two test images of our celeberities, detect faces from each of them and then pass those 
# faces to our trained face recognizer to see if it recognizes them. 
# 
# Below are some utility functions that we will use for drawing bounding box (rectangle) around face and 
# putting celeberity name near the face bounding box. 

# In[8]:

#function to draw rectangle on image 
#according to given (x, y) coordinates and 
#given width and heigh
def draw_rectangle(img, rect):
    (x, y, w, h) = rect
    cv2.rectangle(img, (x, y), (x+w, y+h), (0, 255, 0), 2)
    
#function to draw text on give image starting from
#passed (x, y) coordinates. 
def draw_text(img, text, x, y):
    cv2.putText(img, text, (x, y), cv2.FONT_HERSHEY_PLAIN, 1.5, (0, 255, 0), 2)


# First function `draw_rectangle` draws a rectangle on image based on passed rectangle coordinates. 
# It uses OpenCV's built in function `cv2.rectangle(img, topLeftPoint, bottomRightPoint, rgbColor, lineWidth)` to draw rectangle. 
# We will use it to draw a rectangle around the face detected in test image.
# 
# Second function `draw_text` uses OpenCV's built in function `cv2.putText(img, text, startPoint, font, fontSize, rgbColor, lineWidth)` 
# to draw text on image. 
# 
# Now that we have the drawing functions, we just need to call the face recognizer's `predict(face)` method 
# to test our face recognizer on test images. Following function does the prediction for us.

# In[9]:

#this function recognizes the person in image passed
#and returns a tuple of the label and the username(cardName) 
#subject
def predict(test_img):
    label_text=""
    label_user_id=""
    global subjects
    global subjectsUserIdDict 
    global face_recognizer
    try:
        #make a copy of the image as we don't want to change original image
        img = test_img.copy()
        #detect face from the image
        face, rect = detect_face(img)

        #predict the image using our face recognizer 
        label, confidence = face_recognizer.predict(face)
        #get name of respective label returned by face recognizer
        writeLog("predict( ):>> 'label' is " +str(label) + "\n\t'confidence' is " + str(confidence) + "\n subjects len is " + str(len(subjects)) + "\nDictionaries len is " + str(len(subjectsUserIdDict)) + "\n")
        label_text = subjects[label]
        label_user_id = subjectsUserIdDict[label]
        writeLog("Label_text is " + label_text + "\nLabel User Id is " + str(label_user_id) + "\n")
    except Exception as e:
        fp=open("C:/xampp/htdocs/facepay/python_scripts/errorlog/log.txt","a")
        traceback.print_exc(limit=1, file=fp)
    
    return label_user_id,  label_text
    
def writeLog(msg):
    with open("C:/xampp/htdocs/facepay/python_scripts/errorlog/info.txt","a") as f_log:
        f_log.write(msg)
    

#####Define the starting of the app
if __name__=="__main__":
    app.run(host='0.0.0.0', port=5350)
