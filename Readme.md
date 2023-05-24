BTREE sample


1. Mode to project directory and run npm install to install node_modules
2. Configure database in .env
3. Start servers: symfony server:start and yarn encore dev
4. RUN http://127.0.0.1:8000/api/createroutines to create table + stored procedures


INTERNAL API ENDPOINTS

#1. http://127.0.0.1:8000/api/btree - returns the tree data

#2. http://127.0.0.1:8000/api/btree/getnodeinfo - returns node info

#3. http://127.0.0.1:8000/api/btree/addnode - creates a new node below the provided node guid (it will not allow to insert duplicates)

#4. http://127.0.0.1:8000/api/btree/addsharednode - allows to insert shared node guids
