## Servers

### `POST servers/get/all`

Get info about all servers

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

### `POST servers/get/id`

Get server info by server ID 

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

### `POST servers/get/ip`

Get server info by server IP 

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

### `POST servers/put`

Update information about server

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

### `POST servers/post`

Create new server into database

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

### `POST servers/delete`

Delete server from database

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
