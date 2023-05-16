BTREE sample


1. Mode to project directory and run npm install to install node_modules
2. Run bin/console doctrine:migrations:migrate
3. Start servers: symfony server:start and yarn encore dev
4. open http://127.0.0.1:8000/


INTERNAL API ENDPOINTS
#a. http://127.0.0.1:8000/api/btree - returns the tree data

#b. http://127.0.0.1:8000/api/btree/getpropinfo/{node guid} - returns node info

#c. http://127.0.0.1:8000/api/btree/addnode/{target node guid} - creates a new node below the provided node guid
POST BODY EXAMPLE{

    nodedata = {"New Building":{"PS999999":"Parking Space 999999","shared:SHAREDGUID":"Shared Space1"}}
}

For the shared nodes use guid in following format shared:{guid}. For an example check App\Common::getSampleData()
