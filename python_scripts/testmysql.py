import mysql.connector
#Connect to mysql
conn = mysql.connector.connect(host="localhost", user="root", password="", database="db_facepay")
stmt_cursor = conn.cursor()
stmt_cursor.execute("select * from tbl_user")
for x in stmt_cursor:
    print(x)

print("End of Program")