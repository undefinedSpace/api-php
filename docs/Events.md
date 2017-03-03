## Events

### `POST events/get/all`

Get all events from databases

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
                "description": "<text>"
            },
            ...
        ]
    }
