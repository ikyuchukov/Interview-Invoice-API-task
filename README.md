**Invoice API**
=============

### Requirements:
* docker, docker-compose
### How to start the app:
* Build the docker images

        docker-compose build
* Start the containers

        docker-compose up -d

* Use the Postman collection included in the repo to make a request

### To run the tests:
*  docker exec -it  invoicing-api-php bash
* bin/phpunit to run tests
