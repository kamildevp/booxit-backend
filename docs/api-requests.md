# API Requests

This documentation describes what requests can be made to API and possible responses to those requests.

## Basic Info

### Requests
Every request body except GET and file upload requests must be in json format and contain required parameters.
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
### 2. [Organization](#Organization)
### 4. [Reservation](#Reservation)

## Organization

This section describes organization related requests.

Possible requests:
1. [New organization](#New-organization)
2. [Get organization](#Get-organization)
3. [Modify organization](#Modify-organization)
4. [Delete organization](#Delete-organization)
5. [Add members](#Add-members)
6. [Remove members](#Remove-members)
7. [Modify members](#Modify-members)
8. [Overwrite members](#Overwrite-members)
9. [Add services](#Add-services)
10. [Remove services](#Remove-services)
11. [Modify services](#Modify-services)
12. [Overwrite services](#Overwrite-services)
13. [Get organizations](#Get-organizations)
14. [Get members](#Get-members)
15. [Get services](#Get-services)
16. [Add banner](#Add-banner)
17. [Get banner](#Get-banner)


### New organization
Used to create new organization.

Route: /api/organization

Request Method: POST

Authorization: logged in

Required parameters:
- name - organization name, from 6 to 50 characters long
- description - organization name, from 0 to 2000 characters long

#### Invalid request example:

##### Request Body:
```json
{
    "name": "MyOrganization"
}
```

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Invalid Request",
    "errors": "Parameter description is required"
}
```


#### Valid request example:

##### Request Body:
```json
{
    "name": "MyOrganization",
    "description": ""
}
```

##### Response Body:
```json
{
    "status": "Success",
    "message": "Organization created successfully"
}
```

#### Additional info:
- Once organization is successfully created, user that created it becomes organization admin. 

### Get organization
Used to fetch organization data.

Route: /api/organization/{organizationId}

Request Method: GET

Authorization: not required

Optional query parameters:
- details - comma separated details groups (example query string: ?details=members,services)

    Parameter specifies scope of returned data, allowed details groups:
    * members - returns organization members information
    * services - returns organization services information
    * schedules - returns organization schedules information
    * admins - returns organization admins information

    If no groups are specified only basic information about organization is returned.

- range - returned collections elements range in format "{start}-{end}", used for limiting returned collection elements (example query string: ?range=1-5)

    Parameter can be used to fetch only specific range of requested details,
    for example query string "?details=members&range=5-10" will return organization members starting from fifth member and ending on tenth member.

#### Basic Request Example:

Route: /api/organization/2

##### Response Body:
```json
{
    "name": "MyOrganization",
    "description": "",
    "members_count": "1",
    "services_count": "0",
    "schedules_count": "0"
}
```

#### Request With Details Parameter Example:

Route: /api/organization/2?details=members

##### Response Body:
```json
{
    "name": "MyOrganization",
    "description": "",
    "members_count": "1",
    "services_count": "0",
    "schedules_count": "0",
    "members": [
        {
            "id": 30,
            "user": {
                "id": 23,
                "name": "testName1"
            },
            "roles": [
                "MEMBER",
                "ADMIN"
            ]
        }
    ]
}
```

If organization was not found, invalid request message is returned:
```json
{
    "status": "Failure",
    "message": "Invalid Request",
    "errors": "Organization not found"
}
```

### Modify organization
Used to modify organization by organization member with appropriate credentials.

Route: /api/organization/{organizationId}

Request Method: PATCH

Authorization: organization admin

At least one of parameters is required:
- name - organization name, from 6 to 50 characters long
- description - organization name, from 0 to 2000 characters long

#### Invalid request example:

##### Request Body:
```json
{
    "name": "My"
}
```

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Validation Error",
    "errors": {
        "name": "Minimum name length is 6 characters"
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "name": "MyCompany"
}
```

##### Response Body:
```json
{
    "status": "Success",
    "message": "Organization settings modified successfully"
}
```

### Delete organization
Used to delete organization by organization member with appropriate credentials.

Route: /api/organization/{organizationId}

Request Method: DELETE

Authorization: organization admin

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
    "message": "Organization removed successfully"
}
```

### Add members
Used to add organization members by organization member with appropriate credentials.

Route: /api/organization/{organizationId}/members

Request Method: POST

Authorization: organization admin

Required parameters:
- members - array of members settings:
    - user_id - user id
    - roles - array of member roles, allowed roles: ADMIN, MEMBER

#### Invalid request example:

##### Request Body:
```json
{
    "members": [
        {
            "user_id":25
        },
        {
            "user_id":26
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Invalid Request",
    "errors": "Parameter roles is required"
}
```


#### Valid request example:

##### Request Body:
```json
{
    "members": [
        {
            "user_id":25,
            "roles": ["ADMIN", "MEMBER"]
        },
        {
            "user_id":26,
            "roles": ["MEMBER"]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "Success",
    "message": "Members added successfully"
}
```

### Remove members
Used to remove organization members by organization member with appropriate credentials.

Route: /api/organization/{organizationId}/members

Request Method: DELETE

Authorization: organization admin

Required parameters:
- members - array of members id

#### Invalid request example:

##### Request Body:
```json
{
    "members": [32,33]
}
```

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Invalid Request",
    "errors": "Member with id = 33 does not exist"
}
```


#### Valid request example:

##### Request Body:
```json
{
    "members": [31,32]
}
```

##### Response Body:
```json
{
    "status": "Success",
    "message": "Members removed successfully"
}
```

### Modify members
Used to modify organization members by organization member with appropriate credentials.

Route: /api/organization/{organizationId}/members

Request Method: PATCH

Authorization: organization admin

Required parameters:
- members - array of members settings:
    - id - member id
    - roles - array of member roles, allowed roles: ADMIN, MEMBER

#### Invalid request example:

##### Request Body:
```json
{
    "members": [
        {
            "id":33,
            "roles": ["ADM", "MEMBER"]
        },
        {
            "id":34,
            "roles": ["MEMBER"]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Validation Error",
    "errors": {
        "members": {
            {
                "roles": "Role ADM is not valid member role. Allowed roles: MEMBER, ADMIN"
            }
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "members": [
        {
            "id":33,
            "roles": ["ADMIN", "MEMBER"]
        },
        {
            "id":34,
            "roles": ["MEMBER"]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "Success",
    "message": "Members modified successfully"
}
```

### Overwrite members
Used to overwrite organization members by organization member with appropriate credentials.

Route: /api/organization/{organizationId}/members

Request Method: PUT

Authorization: organization admin

Required parameters:
- members - array of members settings:
    - id - member id (optional - if specified settings will be applied to existing member, preserving member relations to other entities, otherwise its treated as new member)
    - user_id - user id
    - roles - array of member roles, allowed roles: ADMIN, MEMBER

Members not specified in the request will be removed.

#### Invalid request example:

##### Request Body:
```json
{
    "members": [
        {
            "id":33,
            "roles": ["ADMIN", "MEMBER"]
        },
        {
            "roles": ["MEMBER"]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Invalid Request",
    "errors": "Parameter user_id is required"
}
```


#### Valid request example:

##### Request Body:
```json
{
    "members": [
        {
            "id":33,
            "user_id": 27,
            "roles": ["ADMIN", "MEMBER"]
        },
        {
            "user_id": 26,
            "roles": ["MEMBER"]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "Success",
    "message": "Members overwritten successfully"
}
```

### Add services
Used to add organization services by organization member with appropriate credentials.

Route: /api/organization/{organizationId}/services

Request Method: POST

Authorization: organization admin

Required parameters:
- services - array of members settings:
    - name - service name from 6 to 50 characters
    - description - service description from 0 to 255 characters
    - duration - service duration in valid DateInterval format (https://www.php.net/manual/en/class.dateinterval.php), for example: "PT01H30M"
    - estimated_price - string service price from 0 to 10 characters

#### Invalid request example:

##### Request Body:
```json
{
    "services":[
        {
            "name": "MyService",
            "description": "",
            "duration": "PT00H15M",
            "estimated_price": "50PLN"
        },
        {
            "name": "t",
            "description": "description",
            "duration": "PT00H30M",
            "estimated_price": "30 PLN"
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Validation Error",
    "errors": {
        "services": {
            "1": {
                "name": "Minimum name length is 6 characters"
            }
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "services":[
        {
            "name": "MyService",
            "description": "",
            "duration": "PT00H15M",
            "estimated_price": "50PLN"
        },
        {
            "name": "testService2",
            "description": "description",
            "duration": "PT00H30M",
            "estimated_price": "30 PLN"
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "Success",
    "message": "Services added successfully"
}
```

### Remove services
Used to remove organization services by organization member with appropriate credentials.

Route: /api/organization/{organizationId}/members

Request Method: DELETE

Authorization: organization admin

Required parameters:
- services - array of services id

#### Invalid request example:

##### Request Body:
```json
{
    "services":["service1"]
}
```

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Invalid Request",
    "errors": "Parameter services parameter must be array of integers"
}
```


#### Valid request example:

##### Request Body:
```json
{
    "services":[23,24]
}
```

##### Response Body:
```json
{
    "status": "Success",
    "message": "Services removed successfully"
}
```

### Modify services
Used to modify organization services by organization member with appropriate credentials.

Route: /api/organization/{organizationId}/services

Request Method: PATCH

Authorization: organization admin

Required parameters:
- services - array of services settings:
    - id - service id
    - At least one of parameters:
        - name - service name from 6 to 50 characters
        - description - service description from 0 to 255 characters
        - duration - service duration in valid DateInterval format (https://www.php.net/manual/en/class.dateinterval.php), for example: "PT01H30M"
        - estimated_price - string service price from 0 to 10 characters


#### Invalid request example:

##### Request Body:
```json
{
    "services":[
        {
            "id": 25,
            "duration": "PT"
        },
        {
            "id": 26,
            "name": "te",
            "description": "description",
            "duration": "PT00H30M",
            "estimated_price": "30 PLN"
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Validation Error",
    "errors": {
        "services": {
            {
                "duration": "Invalid duration format"
            },
            {
                "name": "Minimum name length is 6 characters"
            }
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "services":[
            {
                "id": 25,
                "duration": "PT01H"
            },
            {
                "id": 26,
                "name": "testService2",
                "description": "description",
                "duration": "PT00H30M",
                "estimated_price": "30 PLN"
            }
    ]
}
```

##### Response Body:
```json
{
    "status": "Success",
    "message": "Services modified successfully"
}
```

### Overwrite services
Used to overwrite organization members by organization member with appropriate credentials.

Route: /api/organization/{organizationId}/members

Request Method: PUT

Authorization: organization admin

Required parameters:
- services - array of services settings:
    - id - service id (optional - if specified settings will be applied to existing service, preserving service relations to other entities, otherwise its treated as new service)
    - name - service name from 6 to 50 characters
    - description - service description from 0 to 255 characters
    - duration - service duration in valid DateInterval format (https://www.php.net/manual/en/class.dateinterval.php), for example: "PT01H30M"
    - estimated_price - string service price from 0 to 10 characters

Services not specified in the request will be removed.

#### Invalid request example:

##### Request Body:
```json
{
    "services":[
            {
                "id": 25,
                "name": "2",
                "description": "description",
                "duration": "PT00H30M",
                "estimated_price": "30 PLN"
            },
            {
                "name": "testService2",
                "description": "description",
                "duration": "PT00H30M",
                "estimated_price": "30 PLN"
            }
    ]
}
```

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Validation Error",
    "errors": {
        "services": {
            {
                "name": "Minimum name length is 6 characters"
            }
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "services":[
            {
                "id": 25,
                "name": "testService7",
                "description": "description",
                "duration": "PT00H30M",
                "estimated_price": "30 PLN"
            },
            {
                "name": "testService3",
                "description": "description",
                "duration": "PT00H30M",
                "estimated_price": "30 PLN"
            }
    ]
}
```

##### Response Body:
```json
{
    "status": "Success",
    "message": "Services overwritten successfully"
}
```


### Get organizations
Used to fetch basic data of multiple organizations.

Route: /api/organizations

Request Method: GET

Authorization: not required

Optional query parameters:
- filter - partial organization name, used to filter organizations list
 (example query string: ?filter=companyname)

- range - returned collection elements range in format "{start}-{end}", used for limiting returned collection elements (example query string: ?range=1-5)

    Parameter can be used to fetch only specific range of requested collection,
    for example query string "?range=5-10" will return organizations starting from fifth organization and ending on tenth organization.

#### Basic Request Example:

Route: /api/organizations

##### Response Body:
```json
[
    {
        "id": 1,
        "name": "Booxit",
        "members_count": "2",
        "services_count": "3",
        "schedules_count": "5"
    },
    {
        "id": 2,
        "name": "MyCompany",
        "members_count": "2",
        "services_count": "0",
        "schedules_count": "0"
    }
]
```

#### Request With Filter Parameter Example:

Route: /api/organizations?filter=booxit

##### Response Body:
```json
[
    {
        "id": 1,
        "name": "Booxit",
        "members_count": "2",
        "services_count": "3",
        "schedules_count": "5"
    }
]
```

### Get members
Used to fetch organization members.

Route: /api/organization/{organizationId}/members

Request Method: GET

Authorization: not required

Optional query parameters:
- filter - partial user name or email, used to filter members list
 (example query string: ?filter=test)

- range - returned collection elements range in format "{start}-{end}", used for limiting returned collection elements (example query string: ?range=1-5)

    Parameter can be used to fetch only specific range of requested collection,
    for example query string "?range=5-10" will return organization members starting from fifth member and ending on tenth member.

#### Basic Request Example:

Route: /api/organization/2/members

##### Response Body:
```json
[
    {
        "id": 35,
        "user": {
            "id": 26,
            "name": "testName4"
        },
        "roles": [
            "MEMBER"
        ]
    },
    {
        "id": 33,
        "user": {
            "id": 27,
            "name": "testName5"
        },
        "roles": [
            "ADMIN",
            "MEMBER"
        ]
    }
]
```

#### Request With Range Parameter Example:

Route: /api/organization/2/members?range=2-2

##### Response Body:
```json
[
    {
        "id": 33,
        "user": {
            "id": 27,
            "name": "testName5"
        },
        "roles": [
            "ADMIN",
            "MEMBER"
        ]
    }
]
```

If organization was not found, invalid request message is returned:
```json
{
    "status": "Failure",
    "message": "Invalid Request",
    "errors": "Organization not found"
}
```

### Get services
Used to fetch organization services.

Route: /api/organization/{organizationId}/services

Request Method: GET

Authorization: not required

Optional query parameters:
- filter - partial service name, used to filter members list
 (example query string: ?filter=test)

- range - returned collection elements range in format "{start}-{end}", used for limiting returned collection elements (example query string: ?range=1-5)

    Parameter can be used to fetch only specific range of requested collection,
    for example query string "?range=5-10" will return organization members starting from fifth member and ending on tenth member.

#### Basic Request Example:

Route: /api/organization/1/services

##### Response Body:
```json
[
    {
        "id": 1,
        "name": "MyService",
        "description": "",
        "duration": "PT00H15M",
        "estimated_price": "50PLN"
    },
    {
        "id": 20,
        "name": "testService2",
        "description": "description",
        "duration": "PT00H30M",
        "estimated_price": "30 PLN"
    },
    {
        "id": 21,
        "name": "testService3",
        "description": "description",
        "duration": "PT00H20M",
        "estimated_price": "40 PLN"
    },
    {
        "id": 22,
        "name": "testService4",
        "description": "description",
        "duration": "PT00H45M",
        "estimated_price": "90 PLN"
    }
]
```

#### Request With Filter And Range Parameters Example:

Route: /api/organization/1/services?filter=test&range=1-2

##### Response Body:
```json
[
    {
        "id": 21,
        "name": "testService3",
        "description": "description",
        "duration": "PT00H20M",
        "estimated_price": "40 PLN"
    },
    {
        "id": 22,
        "name": "testService4",
        "description": "description",
        "duration": "PT00H45M",
        "estimated_price": "90 PLN"
    }
]
```

If organization was not found, invalid request message is returned:
```json
{
    "status": "Failure",
    "message": "Invalid Request",
    "errors": "Organization not found"
}
```

### Add banner
Used to add organization banner by organization member with appropriate credentials.

Route: /api/organization/{organizationId}/banner

Request Method: POST

Authorization: organization admin

Request must include image file which complies with following requirements:
- parameter name: banner
- allowed file types: jpg, jpeg, png
- max file size: 10 MB

Request with curl:
```bash
curl -X POST -H "Authorization: Bearer {token}" -F banner=@image.png https://localhost/api/organization/{$organizationId}/banner
```


#### Invalid request example:

##### Curl Request:
```bash
curl -X POST -F banner=@myImage.png https://localhost/api/organization/2/banner

```

##### Response Body:
```json
{
    "status": "Failure",
    "message": "Invalid Request",
    "errors": "Parameter roles is required"
}
```


#### Valid request example:

##### Curl Request:
```bash
curl -X POST -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2ODMyMDg0ODgsImV4cCI6MTY4MzIxMjA4OCwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidGVzdDVAdGVzdC5jb20ifQ.adTgExi_go4w0_uP5rnmyGjkcaYgE5567XLK4wfuGtYZFCEApUNjaNAhPzededb5HN3gmJy2KKp2Fo4YORinLMX_ISE_9vJKmYSEGL8oo9VeeP7s897ksGgmBBwCbcgfPFSICeTvVgMilanXKzxUXEGYJ6RkblccKlEMWKVBGJ3g0iqZZaDnIoSVJsqJ8WNTHtJ99mEPlaRZ0UHdfdMmDHW2f0U6WCVzqjOZyHHaTwzywJg0if2jwGJld6BtBBOdqtTrFivjplBCDvi2nPCNELYOuja7vDbRdk4d8a4za3NsJ3VFNK-MSWVgDVoVXwrGVVW8eYc3KSkaJAGXSUCAsg" -F banner=@myImage.png https://localhost/api/organization/2/banner

```

##### Response Body:
```json
{
    "status": "Success",
    "message": "Banner uploaded successfully"
}
```

### Get banner
Used to fetch organization banner.

Route: /api/organization/{organizationId}/banner

Request Method: GET

Authorization: not required

#### Basic Request Example:

Route: /api/organization/1/services

##### Response:
Image or empty response if organization banner has not been uploaded.



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

Authorization: organization admin or organization member assigned to reservation schedule

Optional query parameters:
- details - comma separated details groups (example query string: ?details=schedule,service)

    Parameter specifies scope of returned data, allowed details groups:
    * organization - returns basic information about reservation organization
    * schedule - returns basic information about reservation schedule
    * service - returns basic information about reservation service

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