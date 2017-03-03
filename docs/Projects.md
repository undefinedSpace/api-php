## Servers

### `POST projects/get/all`

Get info about all projects

#### Request

Empty

#### Response
    {
        "code": "0",
        "status": "success",
        "description": "Ok",
        "response": [
            {
                "id": "<num>",
                "ip": "<text>",
                "hostname": "<text>",
                "token": "<text>"
            },
            {
                "id": "<num>",
                "ip": "<text>",
                "hostname": "<text>",
                "token": "<text>"
            },
            ...
        ]
    }

### `POST projects/get/id`

Get project info by project ID 

#### Request
    {
        "id": "<int>",
    }

#### Response
    {
        "code": "0",
        "status": "success",
        "description": "Ok",
        "response": [
            {
                "id": "<num>",
                "ip": "<text>",
                "hostname": "<text>",
                "token": "<text>"
            }
        ]
    }

### `POST projects/put`

Update information about project

#### Request
    {
        "id": "<int>",
        "ip": "<text>",
        "hostname": "<text>",
        "token": "<text>",
    }

#### Response
    {
        "code": "0",
        "status": "success",
        "description": "Ok",
        "response": [
            {
                "id": "<num>",
                "ip": "<text>",
                "hostname": "<text>",
                "token": "<text>"
            }
        ]
    }

### `POST projects/post`

Create new project into database

#### Request
    {
        "ip": "<text>",
        "hostname": "<text>",
        "token": "<text>",
    }

#### Response
    {
        "code": "0",
        "status": "success",
        "description": "Ok",
        "response": [
            {
                "id": "<num>",
                "ip": "<text>",
                "hostname": "<text>",
                "token": "<text>"
            }
        ]
    }

### `POST projects/delete`

Delete project from database

#### Request
    {
        "id": "<int>"
    }

#### Response
    {
        "code": "0",
        "status": "success",
        "description": "Ok",
        "response": [
            {
                "id": "<num>",
                "ip": "<text>",
                "hostname": "<text>",
                "token": "<text>"
            }
        ]
    }
