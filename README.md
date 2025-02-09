### How to init the project :
- git clone
- Create an .env.local (cp .env .env.local)
  - ```DATABASE_URL="postgresql://minimaxi:password@postgres:5432/minimaxi?serverVersion=17&charset=utf8"```
- ```make up```
- ```make composer install```
  - go on http://localhost:8080/ if you want to use adminer
    - server : postgres
    - username : minimaxi
    - password : password
- ```make fixtures``` (to load the fixtures, if fixtures file exists)
- ```make npm -- install vue-loader@^17.0.0 --save-dev``` (if you need this, a message will appear)
- ```make npm install```
- ```make npm run watch``` (when you work)
- ```make npm run build``` (in prod)
- go to http://localhost:8000
- Enjoy ! (and hard work 😎)


### Tips for any problem :
- ```make symfony d:d:c``` (to create the database)
- ```make -- symfony d:s:u --force ``` (push the schema of the database)
- Add an .htaccess file in public/ folder if you have a 404 error https://gist.github.com/alch/7766993 (nginx)
- Something else ? Dm me !

---
Comptes de tests :

- Client:
  - mail : client@client.fr
  - password : client
 
- Admin:
  - mail : admin@admin.fr
  - password : admin
 
---

- If you want to add some task, don't hesitate !
