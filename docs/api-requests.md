# API Requests

This documentation describes what requests can be made to API and possible responses to those requests.

## Basic Info

### Requests
Every request body except GET requests must be in json format and contain required parameters.
Parameters may use snake or cammel case.
If request requires user authorization appropriate header with JWT token must passed alongside the request:
```Authorization: Bearer {JWTToken}```

Example request structure:
```json
{
    "schedule_id": 1,
    "date": "2023-04-24",
    "phone_number": "292423411",
    "email": "fake@fake.com",
    "service_id": 1,
    "start_time": "11:05;"
}
```
## Responses
Most responses have json format and share common structure of general parameters:
- status - request status
    * 'Success' - on successful request execution 
    * 'Failure' - if request was not executed due to errors
- message - details about request execution
- errors - Details about errors during request execution

Example response structure:
```json
{
    "status": "Failure",
    "message": "Validation Error",
    "errors": {
        "email": "Value is not a valid email.",
        "phone_number": "Value is not valid phone number"
    }
}
```

## Sections:

### 5. [Reservation](#Reservation)

## Reservation

This section describes reservation related requests.

Possible requests:
1. [New reservation](#New-reservation)
2. [Get reservation](#Get-reservation)
3. [Modify reservation](#Modify-reservation)
4. [Delete reservation](#Delete-reservation)
5. [Confirm reservation](#Confirm-reservation)

### New reservation
Used to create new reservation.

Route: /api/reservation

Request Method: POST

Authorization: not required

Required parameters:
- schedule_id - integer id of schedule where reservation supposed to be made (example: 1)
- date - reservation date in string format 'Y-m-d' (example: "2023-04-24")
- phone_number - string phone number (example: "292423411")
- email - email of person making the reservation (example: "fake@fake.com")
- service_id - integer id of service assigned to schedule (example: 1)
- start_time - reservation time in string format 'H:i' (example: "11:05")

#### Invalid request example:

##### Request Body:
```json
{
    "schedule_id": 100,
    "date": "2023-04-2",
    "phone_number": "11",
    "email": "fake",
    "service_id": 1,
    "start_time": "11:5"
}
```

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Validation Error",
    "errors": {
        "schedule_id": "Schedule not found",
        "start_time": "Start time must be in format H:i",
        "email": "Value is not a valid email.",
        "phone_number": "Value is not valid phone number",
        "date": "Value must be in format Y-m-d"
    }
}
```

If parameters format is correct, but resulting reservation time window is not available following message will be returned:
```json
{
    "status": "Failure",
    "message": "Validation Error",
    "errors": {
        "reservation": "Reservation time window is not available"
    }
}
```

#### Valid request example:

##### Request Body:
```json
{
    "schedule_id": 1,
    "date": "2023-04-24",
    "phone_number": "113242432",
    "email": "fake@fake.com",
    "service_id": 1,
    "start_time": "11:50"
}
```

##### Response Body:
```json
{
    "status": "Success",
    "message": "Reservation created successfully"
}
```

#### Additional info:
- Once reservation is successfully created verification link is sent to email address associated with reservation.
- Reservation not verified within 1 hour is automatically removed.
- When reservation is verified additional email is sent with link which allows to cancel reservation.

### Get reservation
Used to fetch reservation data.

Route: /api/reservation/{reservationId}

Request Method: GET

Authorization: not required

Optional query parameters:
- details - comma separated details groups (example query string: ?details=schedule,service)

    Parameter specifies how much data is returned, allowed details groups:
    * organization - returns basic informations about reservation organization
    * schedule - returns basic informations about reservation schedule
    * service - returns basic informations about reservation service

    If no groups are specified only basic information about reservation is returned.

#### Basic Request Example:

Route: /api/reservation/8

##### Response Body:
```json
{
    "date": "2023-04-24",
    "email": "fake@fake.com",
    "phone_number": "3231313123",
    "time_window": {
        "start_time": "11:10",
        "end_time": "11:25"
    },
    "verified": false,
    "confirmed": false
}
```

#### Request With Details Example:

Route: /api/reservation/8?details=organization,service

##### Response Body:
```json
{
    "organization": {
        "id": 1,
        "name": "Booxit"
    },
    "date": "2023-04-24",
    "email": "fake@fake.com",
    "phone_number": "3231313123",
    "service": {
        "id": 1,
        "name": "MyService",
        "estimated_price": "50zł"
    },
    "time_window": {
        "start_time": "11:10",
        "end_time": "11:25"
    },
    "verified": false,
    "confirmed": false
}
```

If reservation was not found, invalid request message is returned:
```json
{
    "status": "Failure",
    "message": "Invalid Request",
    "errors": "Reservation not found"
}
```

### Modify reservation
Used to modify reservation by organization member with appropriate credentials.

Route: /api/reservation/{reservationId}

Request Method: PATCH

Authorization: organization admin or organization member assigned to reservation schedule

At least one of parameters is required:
- date - reservation date in string format 'Y-m-d' (example: "2023-04-24")
- phone_number - string phone number (example: "292423411")
- service_id - integer id of service assigned to schedule (example: 1)
- start_time - reservation time in string format 'H:i' (example: "11:05")

#### Invalid request example:

##### Request Body:
```json
{
    "schedule_id": 100,
    "date": "2023-04-24",
    "start_time": "11:50"
}
```

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Invalid Request",
    "errors": "Request parameter schedule_id is not allowed"
}
```


#### Valid request example:

##### Request Body:
```json
{
    "date": "2023-04-24",
    "start_time": "11:15"
}
```

##### Response Body:
```json
{
    "status": "Success",
    "message": "Reservation modified successfully"
}
```

### Delete reservation
Used to delete reservation by organization member with appropriate credentials.

Route: /api/reservation/{reservationId}

Request Method: DELETE

Authorization: organization admin or organization member assigned to reservation schedule

#### Unauthorized Request Example:

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Invalid Request",
    "errors": "Access Denied"
}
```

#### Request With appropriate credentials:

##### Response Body:
```json
{
    "status": "Success",
    "message": "Reservation removed successfully"
}
```

### Confirm reservation
Used to mark reservation as confirmed by organization member with appropriate credentials.

Route: /api/reservation_confirm/{reservationId}

Request Method: POST

Authorization: organization admin or organization member assigned to reservation schedule

#### Unauthorized Request Example:

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Invalid Request",
    "errors": "Access Denied"
}
```

#### Request With appropriate credentials:

##### Response Body:
```json
{
    "status": "Success",
    "message": "Reservation confirmed"
}
```