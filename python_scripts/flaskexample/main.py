from flask import Flask, request
import mysql.connector
import os
import cv2
import numpy as np
import urllib.parse

subjects = [] 
dicts = dict()
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
        jsonVal="{'user_id'" +":'" +str(result[0])+"','username':'" +str(result[1]) + "', 'cardName':'" + str(result[2])+"'}"
    else:
        jsonVal="{'user_id'" +":'" +str(user_id)+"','username':'', 'cardName':''}"
    #% str(result[0]) % str(result[1]) % str(result[2])
    return jsonVal

#@app.route("/auth/user/<int:user_id>")
#def authenticate(user_id, training_path):
@app.route("/auth/user/<int:user_id>/<string:training_path>")
def authenticate(user_id, training_path):
    training_path = urllib.parse.unquote_plus(training_path)
    prediction_status=0
    label_user_id=""
    cardName=""    
    try:
        #there is no label 0 in our training data so subject name for index/label 0 is empty   
        subjects, dicts = get_subjects_array_and_dict() 
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
            prediction_status=1
        else:
            prediction_status=0
            label_user_id=""
            cardName=""

    except:
        prediction_status=0
    
    return "{\n'predictionStatus':'"+str(prediction_status) + "',\n'reqUserId':'" + str(user_id) + "',\n'predUserId':'" + str(label_user_id) + "',\n'predCardName':'" + str(cardName) + "',\n'inputImageFilename':'" + str(training_path) + "'\n}"

def getUserAuthFile(user_id):
    sql="select id, userId, imageName from tbl_user_image_auth_reqs where userId=%s LIMIT 1"
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
    index = 0
    cursor.execute("select id, cardName from tbl_user")
    result= cursor.fetchall()
    subjects = []
    dicts = dict()

    for ids in result:
        subjects.append(str(ids[1]))
        dicts[index] = str(ids[0])
        index = index + 1
    
    return subjects, dicts



#function to detect face using OpenCV
def detect_face(img):
    #convert the test image to gray image as opencv face detector expects gray images
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    
    #load OpenCV face detector, I am using LBP which is fast
    #there is also a more accurate but slow Haar classifier
    face_cascade = cv2.CascadeClassifier('opencv-files/lbpcascade_frontalface.xml')

    #let's detect multiscale (some images may be closer to camera than others) images
    #result is a list of faces
    faces = face_cascade.detectMultiScale(gray, scaleFactor=1.2, minNeighbors=5)
    
    #if no faces are detected then return original img
    if (len(faces) == 0):
        return None, None
    
    #under the assumption that there will be only one face,
    #extract the face area
    (x, y, w, h) = faces[0]
    
    #return only the face part of the image
    return gray[y:y+w, x:x+h], faces[0]


#this function will read all persons' training images, detect face from each image
#and will return two lists of exactly same size, one list 
# of faces and another list of labels for each face
def prepare_training_data(data_folder_path):
    
    #------STEP-1--------
    #get the directories (one directory for each subject) in data folder
    dirs = os.listdir(data_folder_path)
    
    #list to hold all subject faces
    faces = []
    #list to hold labels for all subjects
    labels = []
    
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
    #make a copy of the image as we don't want to chang original image
    img = test_img.copy()
    #detect face from the image
    face, rect = detect_face(img)

    #predict the image using our face recognizer 
    label, confidence = face_recognizer.predict(face)
    #get name of respective label returned by face recognizer
    label_text = subjects[label]
    label_user_id = dicts[label]
    return label_user_id,  label_text
    
#####Define the starting of the app
if __name__=="__main__":
    app.run(host='0.0.0.0', port=5350)
