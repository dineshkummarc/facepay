Install
*******
1. Install Python    (https://www.python.org/downloads/windows/)

2. Install Numpy  (Open Command Prompt, in Run as Administrator mode. Type: "pip install numpy" 

3. Install OpenCV (Open Command Prompt in Run as Administrator Mode. Type "pip install opencv"

4. Confirm you have XAMPP (PHP, MariaDB, Apache etc) installed. (https://www.apachefriends.org/download.html)

5. Install Flask. (Open Command Prompt in Run as Administrator mode. Type "pip install flask")

6. Install mysql driver for python. (Open Command Prompt in Run as Administrator mode. Type "python -m pip install mysql-connector-python")

7. Update the table settings for the url of the python api

8. Launch your XAMPP control panel.
	8.1. Start Apache.
	8.2. Start MySql
	8.3. Click on "Admin" button beside "mysql". This opens your browser to show the PHP MyAdmin browser

9. Go to C:\xampp\htdocs

9. download the zip file that I sent to you. Unzip it. YOu will see a folder called "facepay"

10. Copy the unziped folder "facepay" into the C:\xampp\htdocs

11. Open the "facepay" folder. Locate this sub-folder named "sql_scripts". Go to PhpMyAdmin (see step 8.3) and click on "Import"

12. Browse to the location "C:\xampp\htdocs\facepay\sql_scripts" and select the file "create_database.sql"

13. Run it to create a copy of the database on your system.

14. Locate the path: "C:\xampp\htdocs\facepay\python_scripts" 

15. Open command prompt in admin mode and type:
    C:\> cd C:\xampp\htdocs\facepay\python_scripts {Type Enter}

16. Type:
	C:\xampp\htdocs\facepay\python_scripts>set FLASK_APP=main.py {Type Enter}
17. To launch your face recognition application type the fllowing
    C:\xampp\htdocs\facepay\python_scripts>flask run {Type Enter}

18. The command prompt will look like below:

	C:\xampp\htdocs\facepay\python_scripts\flaskexample>flask run
	 * Serving Flask app "main.py"
	 * Environment: production
	   WARNING: This is a development server. Do not use it in a production deployment.
	   Use a production WSGI server instead.
	 * Debug mode: off
	 * Running on http://127.0.0.1:5000/ (Press CTRL+C to quit)

19. Confirm that the Local Binary Patterns Face recognition is up by open a browser tab and typing "http://127.0.0.1:5000/" in the address 

20. Now you are ready to run the app.
   Type in your browser: http://localhost:8080/facepay
   
   