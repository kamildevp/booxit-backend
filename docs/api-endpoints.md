# API Endpoints

This documentation describes API endpoints and possible responses.

## Basic Info

### Requests
Every request body except GET and file upload requests must be in json format and contain required parameters.
Parameters may use snake or cammel case.
If request requires user authorization, appropriate header with JWT token must passed alongside the request:
```Authorization: Bearer {JWTToken}```

### Responses
All json responses follow common [JSend](https://github.com/omniti-labs/jsend) format.

## Sections:
### 1. [User](#User)
### 2. [Organization](#Organization)
### 3. [Schedule](#Schedule)
### 4. [Reservation](#Reservation)

## User

This section describes user related endpoints.

Endpoints:
1. [New user](#New-user)
2. [Login](#Login)
3. [Refresh token](#Refresh-token)
4. [Get user](#Get-user)
5. [Modify user](#Modify-user)
6. [Delete user](#Delete-user)
7. [Get users](#Get-users)
8. [Verify user](#Verify-user)


### New user
Used to create new user account.

Route: /api/user

Request Method: POST

Authorization: not required

Required parameters:
- name - user name, from 6 to 50 characters long
- email - user email
- password - user password, from 8 to 20 characters long, can contain special characters (!#$%?&*) and must have at least one letter and digit

#### Invalid request example:

##### Request Body:
```json
{
    "name": "T1",
    "email": "ada",
    "password": "sda"
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Validation error",
        "errors": {
            "email": "Value is not a valid email.",
            "password": "Password length must be from 8 to 20 characters, can contain special characters(!#$%?&*) and must have at least one letter and digit",
            "name": "Minimum name length is 6 characters"
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "name": "testName",
    "email": "test@test.com",
    "password": "password123"
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Account created successfully"
    }
}
```

#### Additional info:
- After successful account creation, verification link is sent to user email address

### Login
Used to log in.

Route: /api/login_check

Request Method: POST

Authorization: not required

Required parameters:
- email - user email
- password - user password

After successful login following parameters are returned:
- token - Json Web Token, used to check user identity and authorize requests. It is valid for 1 hour and must be passed with request header: ```Authorization: Bearer token```
- refresh token - used to get new Json Web Token, when previous expires. Refreshing tokens is described in [Refresh token](#Refresh-token) section

#### Invalid request example:

##### Request Body:
```json
{
    "email": "test@tes.com",
    "password": "password123"
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Invalid credentials"
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "email": "test@test.com",
    "password": "password123"
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2ODM2NTcwOTEsImV4cCI6MTY4MzY2MDY5MSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidGVzdEB0ZXN0LmNvbSJ9.uMkknj6fgtr4kXX4-wQYVpoBA7iNvjvqfB5-sx1DekUGdPfzWRaTejSV_H8Tyfoj_019dc3zKVpDdi1dqBwU4zpUSd28IGOMvLWZc3CFHAB60kfm4GV10rEYNp9ql0vLN75pYBkDYssliiQUE8sia0EoC42OBvTDTFKLwClPslP56jpE3mEPt13jdF3z6OQ7CzCCtYNs1bljhDq8TXfGZYJOd3KcXKMFkRUG7lby4W7B5FmKrxv_MmzAIShl1b0d1UZTHE3eoYCuANBucWjlOjiZ4B5vpgIHSp_b9LtT-kDda294CMX0WdYYurdCudtafdbWiG1KSP5UOa4JS3Qlbg",
        "refresh_token": "b47c7ec2ec6c14c6bd8009379f098b61310d35e0ca9aeb2697a6988424ae4be9e0c812b5f9ea1402089d8142a9e7e3620568bcba7ced7f7146abe5a9f7531e24"
    }
}
```

#### Additional info:
- Login attempt will fail if user account is not active (email is not verified)

### Refresh token
Used to refresh token.

Route: /api/token/refresh

Request Method: POST

Authorization: not required

Required parameters:
- refresh_token - refresh token received after logging in

If token refresh is successful new token is returned.

#### Invalid request example:

##### Request Body:
```json
{
    "refresh_token": "4ece5a9572d18c2957e08465882cc43959252a1f43b73c71a296313f63b1db8751eea2a2a549c7ed7bbd45fe405b8622a4e0b1d2cd5445b455532854142d687"
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Invalid refresh token"
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "refresh_token": "b47c7ec2ec6c14c6bd8009379f098b61310d35e0ca9aeb2697a6988424ae4be9e0c812b5f9ea1402089d8142a9e7e3620568bcba7ced7f7146abe5a9f7531e24"
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2ODM2NTczODgsImV4cCI6MTY4MzY2MDk4OCwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidGVzdEB0ZXN0LmNvbSJ9.pUNZMekPZLZdfPzbd4pfstoDilEQNPCXwsHoE7lND5fqZjUAiauoabmCY5Z0i96ZhoRMJCmnMuDpf7m-pVJVu98HZNss6ewlR7arOCRXaXsGKqux5c5KhMLvPQrmf555TVPF8I9AqFWTyK4wu7zgeE4LIkP3-_5mvyYf0Vrmwt-z1-bTL0D-BgV5fWTC3YHe5MZDfpi8FAye_6xZ5QkVvhwsofUbqqA2i1sWFzb5H46FcTIVF49gjNuSunBqkClIW1NdmWk1tqDDpvx-TrS3xGJg4z7QJn--BHMCHa9Wm3oIcgw0Owjp3yexQ7qWXgTBk13opWbfcauGiChxBsmNKA",
        "refresh_token": "b47c7ec2ec6c14c6bd8009379f098b61310d35e0ca9aeb2697a6988424ae4be9e0c812b5f9ea1402089d8142a9e7e3620568bcba7ced7f7146abe5a9f7531e24"
    }
}
```

#### Additional info:
- Once user email was modified, refresh token is no longer valid and user must log in again

### Get user
Used to fetch user data.

Route: /api/user/{userId}

Request Method: GET

Authorization: not required

Optional query parameters:
- details - comma separated details groups (example query string: ?details=organizations)

    Parameter specifies scope of returned data, allowed details groups:
    * organizations - returns information about organizations user belongs to

    If no groups are specified only basic information about user is returned.

- range - returned collections elements range in format "{start}-{end}", used for limiting returned collection elements (example query string: ?range=1-5)

    Parameter can be used to fetch only specific range of requested details,
    for example query string "?details=organizations&range=5-10" will return part of organizations collection starting from fifth element and ending on tenth element.

#### Basic Request Example:

Route: /api/user/4

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "name": "testName"
    }
}
```

#### Request With Details Parameter Example:

Route: /api/user/4?details=organizations

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "name": "testName",
        "organizations": [
            {
                "organization": {
                    "id": 1,
                    "name": "MyOrganization"
                },
                "roles": [
                    "MEMBER"
                ]
            }
        ]
    }
}
```

If user was not found, invalid request message is returned:
```json
{
    "status": "fail",
    "data": {
        "message": "User not found"
    }
}
```

#### Additional info:
- User email will be returned only if request is made be authorized users (user himself or admin of user organization)
- When userId = logged_in currently logged in user data will be returned

### Modify user
Used to modify user settings

Route: /api/user

Request Method: PATCH

Authorization: logged in

At least one of parameters is required:
- name - user name, from 6 to 50 characters long
- email - user email
- password - user password, from 8 to 20 characters long, can contain special characters (!#$%?&*) and must have at least one letter and digit, requires additional parameter old_password

#### Invalid request example:

##### Request Body:
```json
{
    "password": "test1234"
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "old_password": "Parameter is required"
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "password": "test1234",
    "old_password": "password123"
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Account settings modified successfully"
    }
}
```

#### Additional info:
- If user email was modified, verification link is sent to new email address and only once it is verfied email changes will take effect.

### Delete user
Used to delete user account.

Route: /api/user

Request Method: DELETE

Authorization: logged in

#### Unauthorized Request Example:

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Access denied"
    }
}
```

#### Request With appropriate credentials:

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Account removed successfully"
    }
}
```

#### Additional info:
- If user is only admin in organization, that organization will be removed as well


### Get users
Used to fetch basic data of multiple users.

Route: /api/users

Request Method: GET

Authorization: not required

Optional query parameters:
- filter - partial user name or email, used to filter users list
 (example query string: ?filter=testname)

- range - returned collection elements range in format "{start}-{end}", used for limiting returned collection elements (example query string: ?range=1-5)

    Parameter can be used to fetch only specific range of requested collection,
    for example query string "?range=5-10" will return users collection starting from fifth element and ending on tenth element.

#### Basic Request Example:

Route: /api/users

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "id": 2,
            "name": "TestName2"
        },
        {
            "id": 3,
            "name": "TestName3"
        },
        {
            "id": 4,
            "name": "testName"
        }
    ]
}
```

#### Request With Filter Parameter Example:

Route: /api/users?filter=name3

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "id": 3,
            "name": "TestName3"
        }
    ]
}
```

### Verify user
Used to verify user email address.

Route: /api/user_verify?{query-string-with-signature}

Request Method: GET

Authorization: not required

Should only be accessed by clicking on verification link sent in email message. Returns html view with information about verification result.


## Organization

This section describes organization related endpoints.

Endpoints:
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
16. [Get schedules](#Get-schedules)
17. [Add banner](#Add-banner)
18. [Get banner](#Get-banner)


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
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "description": "Parameter is required"
        }
    }
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
    "status": "success",
    "data": {
        "message": "Organization created successfully"
    }
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

Route: /api/organization/1

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "name": "MyOrganization",
        "description": "desc",
        "members_count": "2",
        "services_count": "4",
        "schedules_count": "4"
    }
}
```

#### Request With Details Parameter Example:

Route: /api/organization/2?details=members

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "name": "MyOrganization",
        "description": "desc",
        "members_count": "2",
        "services_count": "4",
        "schedules_count": "4",
        "members": [
            {
                "id": 1,
                "user": {
                    "id": 1,
                    "email": "test1@test.com",
                    "name": "testName"
                },
                "roles": [
                    "MEMBER",
                    "ADMIN"
                ]
            },
            {
                "id": 2,
                "user": {
                    "id": 2,
                    "email": "test2@test.com",
                    "name": "testName"
                },
                "roles": [
                    "MEMBER"
                ]
            }
        ]
    }
}
```

If organization was not found, invalid request message is returned:
```json
{
    "status": "fail",
    "data": {
        "message": "Organization not found"
    }
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
    "status": "fail",
    "data": {
        "message": "Validation error",
        "errors": {
            "name": "Minimum name length is 6 characters"
        }
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
    "status": "success",
    "data": {
        "message": "Organization settings modified successfully"
    }
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
    "status": "fail",
    "data": {
        "message": "Access denied"
    }
}
```

#### Request With appropriate credentials:

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Organization removed successfully"
    }
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
            "user_id":6,
            "roles": ["MEMBER"]
        },
        {
            "user_id":7
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "members": {
                "1": {
                    "roles": "Parameter is required"
                }
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
            "user_id":6,
            "roles": ["ADMIN", "MEMBER"]
        },
        {
            "user_id":7,
            "roles": ["MEMBER"]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Members added successfully"
    }
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
    "members": [8,33]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "members": [
                "Member with id = 33 does not exist"
            ]
        }
    }
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
    "status": "success",
    "data": {
        "message": "Members removed successfully"
    }
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
            "id":2,
            "roles": ["MEMBER"]
        },
        {
            "id":3,
            "roles": ["ADM", "MEMBER"]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Validation error",
        "errors": {
            "members": {
                "1": {
                    "roles": "Role ADM is not valid member role. Allowed roles: MEMBER, ADMIN"
                }
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
            "id":2,
            "roles": ["MEMBER"]
        },
        {
            "id":3,
            "roles": ["ADMIN", "MEMBER"]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Members modified successfully"
    }
}
```

### Overwrite members
Used to overwrite organization members by organization member with appropriate credentials.

Route: /api/organization/{organizationId}/members

Request Method: PUT

Authorization: organization admin

Required parameters:
- members - array of members settings:
    - id - member id (if null new member will be added)
    - user_id - user id
    - roles - array of member roles, allowed roles: ADMIN, MEMBER

Members not specified in the request will be removed.

#### Invalid request example:

##### Request Body:
```json
{
    "members": [
        {
            "id": 1,
            "user_id": 1,
            "roles": [
                "MEMBER",
                "ADMIN"
            ]
        },
        {
            "id": 2,
            "user_id": 2,
            "roles": [
                "MEMBER"
            ]
        },
        {
            "id": 3,
            "roles": [
                "ADMIN",
                "MEMBER"
            ]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "members": {
                "2": {
                    "user_id": "Parameter is required"
                }
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
            "id": 1,
            "user_id": 1,
            "roles": [
                "MEMBER",
                "ADMIN"
            ]
        },
        {
            "id": 2,
            "user_id": 2,
            "roles": [
                "MEMBER"
            ]
        },
        {
            "id": null,
            "user_id": 3,
            "roles": [
                "ADMIN",
                "MEMBER"
            ]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Members overwritten successfully"
    }
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
    "status": "fail",
    "data": {
        "message": "Validation error",
        "errors": {
            "services": {
                "1": {
                    "name": "Minimum name length is 6 characters"
                }
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
    "status": "success",
    "data": {
        "message": "Services added successfully"
    }
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
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "services": "Parameter must be array of integers"
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "services":[6,7]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Services removed successfully"
    }
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
            "id": 3,
            "duration": "PT"
        },
        {
            "id": 5,
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
    "status": "fail",
    "data": {
        "message": "Validation error",
        "errors": {
            "services": [
                {
                    "duration": "Invalid duration format"
                },
                {
                    "name": "Minimum name length is 6 characters"
                }
            ]
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
                "id": 3,
                "duration": "PT01H"
            },
            {
                "id": 5,
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
    "status": "success",
    "data": {
        "message": "Services modified successfully"
    }
}
```

### Overwrite services
Used to overwrite organization members by organization member with appropriate credentials.

Route: /api/organization/{organizationId}/members

Request Method: PUT

Authorization: organization admin

Required parameters:
- services - array of services settings:
    - id - service id (if null new service will be created)
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
                "id": 1,
                "name": "2",
                "description": "description",
                "duration": "PT00H30M",
                "estimated_price": "30 PLN"
            },
            {
                "id": 2,
                "name": "TestService2",
                "description": "",
                "duration": "PT01H30M",
                "estimated_price": "30 PLN"
            },
            {
                "id": null,
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
    "status": "fail",
    "data": {
        "message": "Validation error",
        "errors": {
            "services": [
                {
                    "name": "Minimum name length is 6 characters"
                }
            ]
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
                "id": 1,
                "name": "TestService1",
                "description": "description",
                "duration": "PT00H30M",
                "estimated_price": "30 PLN"
            },
            {
                "id": 2,
                "name": "TestService2",
                "description": "",
                "duration": "PT01H30M",
                "estimated_price": "30 PLN"
            },
            {
                "id": null,
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
    "status": "success",
    "data": {
        "message": "Services overwritten successfully"
    }
}
```


### Get organizations
Used to fetch basic data of multiple organizations.

Route: /api/organization

Request Method: GET

Authorization: not required

Optional query parameters:
- filter - partial organization name, used to filter organizations list
 (example query string: ?filter=companyname)

- range - returned collection elements range in format "{start}-{end}", used for limiting returned collection elements (example query string: ?range=1-5)

    Parameter can be used to fetch only specific range of requested collection,
    for example query string "?range=5-10" will return organizations starting from fifth organization and ending on tenth organization.

#### Basic Request Example:

Route: /api/organization

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "MyOrganization",
            "members_count": "3",
            "services_count": "3",
            "schedules_count": "4"
        },
        {
            "id": 3,
            "name": "MyCompany",
            "members_count": "1",
            "services_count": "0",
            "schedules_count": "0"
        }
    ]
}
```

#### Request With Filter Parameter Example:

Route: /api/organization?filter=org

##### Response Body:
```json
[
    {
        "status": "success",
        "data": [
            {
                "id": 1,
                "name": "MyOrganization",
                "members_count": "3",
                "services_count": "3",
                "schedules_count": "4"
            }
        ]
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

Route: /api/organization/1/members

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "user": {
                "id": 1,
                "email": "test1@test.com",
                "name": "testName"
            },
            "roles": [
                "MEMBER",
                "ADMIN"
            ]
        },
        {
            "id": 2,
            "user": {
                "id": 2,
                "email": "test2@test.com",
                "name": "testName"
            },
            "roles": [
                "MEMBER"
            ]
        },
        {
            "id": 9,
            "user": {
                "id": 3,
                "email": "test3@test.com",
                "name": "testName"
            },
            "roles": [
                "ADMIN",
                "MEMBER"
            ]
        }
    ]
}
```

#### Request With Range Parameter Example:

Route: /api/organization/1/members?range=2-2

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "id": 2,
            "user": {
                "id": 2,
                "email": "test2@test.com",
                "name": "testName"
            },
            "roles": [
                "MEMBER"
            ]
        }
    ]
}
```

If organization was not found, invalid request message is returned:
```json
{
    "status": "fail",
    "data": {
        "message": "Organization not found"
    }
}
```

### Get services
Used to fetch organization services.

Route: /api/organization/{organizationId}/services

Request Method: GET

Authorization: not required

Optional query parameters:
- filter - partial service name, used to filter services list
 (example query string: ?filter=test)

- range - returned collection elements range in format "{start}-{end}", used for limiting returned collection elements (example query string: ?range=1-5)

    Parameter can be used to fetch only specific range of requested collection,
    for example query string "?range=5-10" will return services collection starting from fifth element and ending on tenth element.

#### Basic Request Example:

Route: /api/organization/1/services

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "TestService1",
            "description": "description",
            "duration": "PT00H30M",
            "estimated_price": "30 PLN"
        },
        {
            "id": 2,
            "name": "MyService2",
            "description": "",
            "duration": "PT01H30M",
            "estimated_price": "30 PLN"
        },
        {
            "id": 8,
            "name": "testService3",
            "description": "description",
            "duration": "PT00H30M",
            "estimated_price": "30 PLN"
        }
    ]
}
```

#### Request With Filter And Range Parameters Example:

Route: /api/organization/1/services?filter=test&range=1-2

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "TestService1",
            "description": "description",
            "duration": "PT00H30M",
            "estimated_price": "30 PLN"
        },
        {
            "id": 8,
            "name": "testService3",
            "description": "description",
            "duration": "PT00H30M",
            "estimated_price": "30 PLN"
        }
    ]
}
```

If organization was not found, invalid request message is returned:
```json
{
    "status": "fail",
    "data": {
        "message": "Organization not found"
    }
}
```

### Get schedules
Used to fetch organization schedules.

Route: /api/organization/{organizationId}/schedules

Request Method: GET

Authorization: not required

Optional query parameters:
- filter - partial schedule name, used to filter schedule list
 (example query string: ?filter=test)

- range - returned collection elements range in format "{start}-{end}", used for limiting returned collection elements (example query string: ?range=1-5)

    Parameter can be used to fetch only specific range of requested collection,
    for example query string "?range=5-10" will return collection starting from fifth element and ending on tenth element.

#### Basic Request Example:

Route: /api/organization/1/schedules

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "MySchedule"
        },
        {
            "id": 2,
            "name": "TestSchedule2"
        },
        {
            "id": 4,
            "name": "MySchedule2"
        }
    ]
}
```

#### Request With Filter And Range Parameters Example:

Route: /api/organization/1/schedules?filter=test&range=1-1

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "MySchedule"
        }
    ]
}
```

If organization was not found, invalid request message is returned:
```json
{
    "status": "fail",
    "data": {
        "message": "Organization not found"
    }
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
curl -X POST -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2ODM2NjYyNTgsImV4cCI6MTY4MzY2OTg1OCwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidGVzdDFAdGVzdC5jb20ifQ.ow5Wi-XnJ5j2vjGWod7HiiV3z1dTMKlFjfPuZVa1ycG5e4t65HCOpHp6z05FUN0xkYvJsCmAV3xAyBlsME8hNSBcCsM3rO4kiMfEWsykwwST632RD9yq8e9DERP-YQHAUPAiNc9N_s8HMKUrFwutxZkHbuHPDovN9RD_RVNH--UlRee5qH8TPi3f1McJOXZ2FRho2tCfxDW0qc-DkR0vlthDv4wgAJZww3Y2gEFhMm4GX8xrf5lhElsse_544os5E_sbuBSwnNqyUnfdZC1A0yMRgvaxBwi6J1Am9o2bcFMZMPWQ90msgYAnRn6UfvK3LArKgUbSmmeJceNnSi-nPA" -F banner=@myFile.txt https://localhost/api/organization/1/banner
```

##### Response Body:
```json
{
    "status":"fail",
    "data": {
        "message":"Validation error",
        "errors": {
            "banner":"Invalid file type"
        }
    }
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
    "status":"success",
    "data": {
        "message":"Banner uploaded successfully"
    }
}
```

### Get banner
Used to fetch organization banner.

Route: /api/organization/{organizationId}/banner

Request Method: GET

Authorization: not required

#### Basic Request Example:

Route: /api/organization/1/banner

##### Response:
Image or empty response if organization banner has not been uploaded.


## Schedule

This section describes schedule related endpoints.

Endpoints:
1. [New schedule](#New-schedule)
2. [Get schedule](#Get-schedule)
3. [Modify schedule](#Modify-schedule)
4. [Delete schedule](#Delete-schedule)
5. [Add services](#Add-services)
6. [Remove services](#Remove-services)
7. [Overwrite services](#Overwrite-services)
8. [Add assignments](#Add-assignments)
9. [Remove assignments](#Remove-assignments)
10. [Modify assignments](#Modify-assignments)
11. [Overwrite assignments](#Overwrite-assignments)
12. [Add working hours](#Add-working-hours)
13. [Remove working hours](#Remove-working-hours)
14. [Modify working hours](#Modify-working-hours)
15. [Overwrite working hours](#Overwrite-working-hours)
16. [Get services](#Get-services)
17. [Get assignments](#Get-assignments)
18. [Get working hours](#Get-working-hours)
19. [Get free terms](#Get-free-terms)
20. [Get reservations](#Get-reservations)

### New schedule
Used to create new schedule.

Route: /api/schedule

Request Method: POST

Authorization: organization admin

Required parameters:
- organization_id - organization id
- name -  name, from 6 to 50 characters long
- description - schedule description, from 0 to 500 characters long

#### Invalid request example:

##### Request Body:
```json
{
    "organization_id": 1,
    "name": "My",
    "description": ""
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Validation Error",
        "errors": {
            "name": "Minimum name length is 6 characters"
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "organization_id": 1,
    "name": "MySchedule2",
    "description": ""
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Schedule created successfully"
    }
}
```


### Get schedule
Used to fetch schedule data.

Route: /api/schedule/{scheduleId}

Request Method: GET

Authorization: not required

Optional query parameters:
- details - comma separated details groups (example query string: ?details=services, working_hours)

    Parameter specifies scope of returned data, allowed details groups:
    * services - returns schedule services information
    * assignments - returns schedules assignments information
    * working_hours - returns schedule working hours information

    If no groups are specified only basic information about schedule is returned.

- range - returned collections elements range in format "{start}-{end}", used for limiting returned collection elements (example query string: ?range=1-5)

    Parameter can be used to fetch only specific range of requested details,
    for example query string "?details=services&range=5-10" will return schedule services collection starting from fifth element and ending on tenth element.

#### Basic Request Example:

Route: /api/schedule/1

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "organization": {
            "id": 1,
            "name": "MyOrganization"
        },
        "name": "MySchedule",
        "description": ""
    }
}
```

#### Request With Details Parameter Example:

Route: /api/schedule/1?details=services

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "organization": {
            "id": 1,
            "name": "MyOrganization"
        },
        "name": "MySchedule",
        "description": "",
        "services": [
            {
                "id": 1,
                "name": "TestService1",
                "duration": "PT00H30M",
                "estimated_price": "30 PLN"
            },
            {
                "id": 2,
                "name": "MyService2",
                "duration": "PT01H30M",
                "estimated_price": "30 PLN"
            }
        ]
    }
}
```

If schedule was not found, invalid request message is returned:
```json
{
    "status": "fail",
    "data": {
        "message": "Schedule not found"
    }
}
```

### Modify schedule
Used to modify schedule by organization member with appropriate credentials.

Route: /api/schedule/{scheduleId}

Request Method: PATCH

Authorization: organization admin or member assigned to schedule with write access

At least one of parameters is required:
- name -  name, from 6 to 50 characters long
- description - schedule description, from 0 to 500 characters long

#### Invalid request example:

##### Request Body:
```json
{
    "name": "My",
    "description": ""
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Validation Error",
        "errors": {
            "name": "Minimum name length is 6 characters"
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "name": "MySchedule",
    "description": ""
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Schedule settings modified successfully"
    }
}
```

### Delete schedule
Used to delete schedule by organization member with appropriate credentials.

Route: /api/schedule/{scheduleId}

Request Method: DELETE

Authorization: organization admin or member assigned to schedule with write access

#### Unauthorized Request Example:

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Access denied"
    }
}
```

#### Request With appropriate credentials:

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Schedule removed successfully"
    }
}
```

### Add services
Used to add schedule services by organization member with appropriate credentials.

Route: /api/schedule/{scheduleId}/services

Request Method: POST

Authorization: organization admin or member assigned to schedule with write access

Required parameters:
- services - array of services id

#### Invalid request example:

##### Request Body:
```json
{
    "services": [
        20,13
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "services": [
                "Service with id = 20 does not exist",
                "Service with id = 13 does not exist"
            ]
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "services": [
        8,9
    ]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Services added successfully"
    }
}
```

### Remove services
Used to remove organization members by organization member with appropriate credentials.

Route: /api/schedule/{scheduleId}/services

Request Method: DELETE

Authorization: organization admin or member assigned to schedule with write access

Required parameters:
- services - array of services id

#### Invalid request example:

##### Request Body:
```json
{
    "services": [
        2,124
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "services": [
                "Service with id = 124 is not assigned to schedule"
            ]
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "members": [1,2]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Services removed successfully"
    }
}
```

### Overwrite services
Used to overwrite schedule services by organization member with appropriate credentials.

Route: /api/schedule/{scheduleId}/services

Request Method: PUT

Authorization: organization admin or member assigned to schedule with write access

Required parameters:
- services - array of services id

Services not specified in the request will be removed.

#### Invalid request example:

##### Request Body:
```json
{
    "services": [
        124,7
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "services": [
                "Service with id = 124 does not exist",
                "Service with id = 7 does not exist"
            ]
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "services": [
        1,2,8,9
    ]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Services overwritten successfully"
    }
}
```

### Add assignments
Used to add schedule assignments by organization member with appropriate credentials.

Route: /api/schedule/{scheduleId}/assignments

Request Method: POST

Authorization: organization admin or member assigned to schedule with write access

Required parameters:
- member_id - member id
- access_type - access type. Allowed values: READ, WRITE

#### Invalid request example:

##### Request Body:
```json
{
    "assignments": [
        {
        "member_id": 2,
        "access_type": "WR"
        },
        {
        "member_id": 9,
        "access_type": "WRITE"
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Validation Error",
        "errors": {
            "assignments": [
                {
                    "access_type": "Invalid access type. Allowed access types: READ, WRITE"
                }
            ]
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "assignments": [
        {
        "member_id": 2,
        "access_type": "READ"
        },
        {
        "member_id": 9,
        "access_type": "WRITE"
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Assignments added successfully"
    }
}
```

### Remove assignments
Used to remove schedule assignments by organization member with appropriate credentials.

Route: /api/schedule/{scheduleId}/assignments

Request Method: DELETE

Authorization: organization admin

Required parameters:
- assignments - array of assignments id

#### Invalid request example:

##### Request Body:
```json
{
    "assignments": [
        12,52
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "assignments": [
                "Assignment with id = 52 does not exist"
            ]
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "assignments": [
        12,13
    ]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Assignments removed successfully"
    }
}
```

### Modify assignments
Used to modify schedule assignments by organization member with appropriate credentials.

Route: /api/schedule/{scheduleId}/assignments

Request Method: PATCH

Authorization: organization admin

Required parameters:
- id - assignment id
- At least one of parameters:
    - member_id - member id
    - access_type - access type. Allowed values: READ, WRITE

#### Invalid request example:

##### Request Body:
```json
{
    "assignments": [
        {
            "id": 14,
            "access_type": "AD"
        },
        {
            "id": 15,
            "access_type": "READ"
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Validation Error",
        "errors": {
            "assignments": [
                {
                    "access_type": "Invalid access type. Allowed access types: READ, WRITE"
                }
            ]
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "assignments": [
        {
            "id": 14,
            "access_type": "WRITE"
        },
        {
            "id": 15,
            "access_type": "READ"
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Assignments modified successfully"
    }
}
```

### Overwrite assignments
Used to overwrite schedule assignments by organization member with appropriate credentials.

Route: /api/schedule/{scheduleId}/assignments

Request Method: PUT

Authorization: organization admin
Required parameters:
- id - assignment id (if null new assignment will be created)
- member_id - member id
- access_type - access type. Allowed values: READ, WRITE

Assignments not specified in the request will be removed.

#### Invalid request example:

##### Request Body:
```json
{
    "assignments": [
        {
            "id": 14,
            "member_id": 2,
            "access_type": "WRITE"
        },
        {
            "id": 15,
            "access_type": "READ"
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "assignments": {
                "1": {
                    "member_id": "Parameter is required"
                }
            }
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "assignments": [
        {
            "id": null,
            "member_id": 2,
            "access_type": "WRITE"
        },
        {
            "id": 15,
            "member_id": 9,
            "access_type": "READ"
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Assignments overwritten successfully"
    }
}
```

### Add working hours
Used to add schedule working hours by organization member with appropriate credentials.

Route: /api/schedule/{scheduleId}/working_hours

Request Method: POST

Authorization: organization admin or member assigned to schedule with write access

Required parameters:
- day - day of the week or specific date in format Y-m-d
- time_windows - day time windows, array of time window settings:
    - start_time - start time in format H:i
    - end_time - end time in format H:i

#### Invalid request example:

##### Request Body:
```json
{
    "working_hours": [
        {
            "day": "2023-05-18",
            "time_windows": [
                {
                    "start_time": "08:00",
                    "end_time": "10:00"
                },
                {
                    "start_time": "15:00",
                    "end_time": "20:00"
                }
            ]
        },
        {
            "day": "tuesday",
            "time_windows": [
                {
                    "start_time": "08:00",
                    "end_time": "11:00"
                },
                {
                    "start_time": "10:00",
                    "end_time": "17:00"
                }
            ]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Validation Error",
        "errors": {
            "working_hours": {
                "1": {
                    "time_windows": "Time windows are overlaping"
                }
            }
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "working_hours": [
        {
            "day": "2023-05-18",
            "time_windows": [
                {
                    "start_time": "08:00",
                    "end_time": "10:00"
                },
                {
                    "start_time": "15:00",
                    "end_time": "20:00"
                }
            ]
        },
        {
            "day": "tuesday",
            "time_windows": [
                {
                    "start_time": "08:00",
                    "end_time": "11:00"
                },
                {
                    "start_time": "12:00",
                    "end_time": "17:00"
                }
            ]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Working hours added successfully"
    }
}
```

#### Additional info:
- Working hours specified for specific date have priority over working hours specified for day of week, meaning date working hours are used when free terms are calculated


### Remove working hours
Used to remove schedule working hours by organization member with appropriate credentials.

Route: /api/schedule/{scheduleId}/working_hours

Request Method: DELETE

Authorization: organization admin or member assigned to schedule with write access

Required parameters:
- working_hours - array of day names or dates in format Y-m-d

#### Invalid request example:

##### Request Body:
```json
{
    "working_hours": [
        "monday"
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "working_hours": [
                "Working hours for monday are not defined"
            ]
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "working_hours": [
        "tuesday", "2023-05-18"
    ]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Working hours removed successfully"
    }
}
```


### Modify working hours
Used to modify schedule working hours by organization member with appropriate credentials.

Route: /api/schedule/{scheduleId}/working_hours

Request Method: PATCH

Authorization: organization admin

Required parameters:
- day - day of the week or date in format Y-m-d
- time_windows - day time windows, array of time window settings:
    - start_time - start time in format H:i
    - end_time - end time in format H:i


#### Invalid request example:

##### Request Body:
```json
{
    "working_hours": [
        {
            "day": "monday",
            "time_windows": [
                {
                    "start_time": "12:00",
                    "end_time": "17:00"
                }
            ]
        },
        {
            "day": "2023-05-18",
            "time_windows": [
                {
                    "start_time": "08:00",
                    "end_time": "10:00"
                },
                {
                    "start_time": "15:00",
                    "end_time": "20:00"
                }
            ]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "working_hours": [
                {
                    "day": "Working hours for monday are not defined"
                }
            ]
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "working_hours": [
        {
            "day": "tuesday",
            "time_windows": [
                {
                    "start_time": "12:00",
                    "end_time": "17:00"
                }
            ]
        },
        {
            "day": "2023-05-18",
            "time_windows": [
                {
                    "start_time": "08:00",
                    "end_time": "10:00"
                },
                {
                    "start_time": "15:00",
                    "end_time": "20:00"
                }
            ]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Working hours modified successfully"
    }
}
```

#### Additional info:
- Working hours specified for specific date have priority over working hours specified for day of week, meaning date working hours are used when free terms are calculated

### Overwrite working hours
Used to overwrite schedule working hours by organization member with appropriate credentials.

Route: /api/schedule/{scheduleId}/working_hours

Request Method: PUT

Authorization: organization admin or member assigned to schedule with write access
Required parameters:
- day - day of the week or date in format Y-m-d
- time_windows - day time windows, array of time window settings:
    - start_time - start time in format H:i
    - end_time - end time in format H:i

Working hours not specified in the request will be removed.

#### Invalid request example:

##### Request Body:
```json
{
    "working_hours": [
        {
            "day": "tuesday",
            "time_windows": [
                {
                    "start_time": "12",
                    "end_time": "17:00"
                }
            ]
        },
        {
            "day": "2023-05-18",
            "time_windows": [
                {
                    "start_time": "08:00",
                    "end_time": "10:00"
                },
                {
                    "start_time": "15:00",
                    "end_time": "20:00"
                }
            ]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Validation Error",
        "errors": {
            "working_hours": [
                {
                    "time_windows": [
                        {
                            "start_time": "Value is not valid time format. Supported time format: H:i"
                        }
                    ]
                }
            ]
        }
    }
}
```

#### Additional info:
- Working hours specified for specific date have priority over working hours specified for day of week, meaning date working hours are used when free terms are calculated

#### Valid request example:

##### Request Body:
```json
{
    "working_hours": [
        {
            "day": "friday",
            "time_windows": [
                {
                    "start_time": "08:00",
                    "end_time": "18:00"
                }
            ]
        },
        {
            "day": "sunday",
            "time_windows": [
                {
                    "start_time": "08:00",
                    "end_time": "10:00"
                },
                {
                    "start_time": "12:00",
                    "end_time": "20:00"
                }
            ]
        }
    ]
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Working hours overwritten successfully"
    }
}
```

### Get services
Used to fetch schedule services.

Route: /api/schedule/{scheduleId}/services

Request Method: GET

Authorization: not required

Optional query parameters:
- filter - partial service name, used to filter services list
 (example query string: ?filter=test)

- range - returned collection elements range in format "{start}-{end}", used for limiting returned collection elements (example query string: ?range=1-5)

    Parameter can be used to fetch only specific range of requested collection,
    for example query string "?range=5-10" will return collection elements starting from fifth element and ending on tenth element.

#### Basic Request Example:

Route: /api/schedule/1/services

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "TestService1",
            "duration": "PT00H30M",
            "estimated_price": "30 PLN"
        },
        {
            "id": 2,
            "name": "MyService2",
            "duration": "PT01H30M",
            "estimated_price": "30 PLN"
        },
        {
            "id": 8,
            "name": "testService3",
            "duration": "PT00H30M",
            "estimated_price": "30 PLN"
        },
        {
            "id": 9,
            "name": "MyService",
            "duration": "PT00H20M",
            "estimated_price": "70 PLN"
        }
    ]
}
```

#### Request With Range Parameter Example:

Route: /api/schedule/1/services?range=2-3

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "id": 2,
            "name": "MyService2",
            "duration": "PT01H30M",
            "estimated_price": "30 PLN"
        },
        {
            "id": 8,
            "name": "testService3",
            "duration": "PT00H30M",
            "estimated_price": "30 PLN"
        }
    ]
}
```

If schedule was not found, invalid request message is returned:
```json
{
    "status": "fail",
    "data": {
        "message": "Schedule not found"
    }
}
```

### Get assignments
Used to fetch schedule assignments.

Route: /api/schedule/{scheduleId}/assignments

Request Method: GET

Authorization: not required

Optional query parameters:
- filter - partial assigned member name or email, used to filter assignments list
 (example query string: ?filter=test)

- range - returned collection elements range in format "{start}-{end}", used for limiting returned collection elements (example query string: ?range=1-5)

    Parameter can be used to fetch only specific range of requested collection,
    for example query string "?range=5-10" will return collection elements starting from fifth element and ending on tenth element.

#### Basic Request Example:

Route: /api/schedule/1/assignments

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "id": 15,
            "member": {
                "id": 9,
                "user": {
                    "id": 3,
                    "email": "test3@test.com",
                    "name": "testName"
                }
            },
            "access_type": "READ"
        },
        {
            "id": 16,
            "member": {
                "id": 2,
                "user": {
                    "id": 2,
                    "email": "test2@test.com",
                    "name": "testName"
                }
            },
            "access_type": "WRITE"
        }
    ]
}
```

#### Request With Filter Parameter Example:

Route: /api/schedule/1/assignments?filter=test3@test

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "id": 15,
            "member": {
                "id": 9,
                "user": {
                    "id": 3,
                    "email": "test3@test.com",
                    "name": "testName"
                }
            },
            "access_type": "READ"
        }
    ]
}
```

If schedule was not found, invalid request message is returned:
```json
{
    "status": "fail",
    "data": {
        "message": "Schedule not found"
    }
}
```

### Get working hours
Used to fetch schedule working hours.

Route: /api/schedule/{scheduleId}/working_hours

Request Method: GET

Authorization: not required

Optional query parameters:
- filter - partial day name or date, used to filter working hours list
 (example query string: ?filter=monday)
    
    If filter is full date and working hours have not been found for this date, working hours for corresponding day of the week will be matched

- range - returned collection elements range in format "{start}-{end}", used for limiting returned collection elements (example query string: ?range=1-5)

    Parameter can be used to fetch only specific range of requested collection,
    for example query string "?range=5-10" will return collection elements starting from fifth element and ending on tenth element.

#### Basic Request Example:

Route: /api/schedule/1/working_hours

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "day": "friday",
            "time_windows": [
                {
                    "start_time": "08:00",
                    "end_time": "18:00"
                }
            ]
        },
        {
            "day": "sunday",
            "time_windows": [
                {
                    "start_time": "08:00",
                    "end_time": "10:00"
                },
                {
                    "start_time": "12:00",
                    "end_time": "20:00"
                }
            ]
        },
        {
            "day": "2023-05-18",
            "time_windows": [
                {
                    "start_time": "08:00",
                    "end_time": "10:00"
                },
                {
                    "start_time": "15:00",
                    "end_time": "20:00"
                }
            ]
        }
    ]
}
```

#### Request With Filter Parameter Example:

Route: /api/schedule/1/working_hours?filter=sunday

##### Response Body:
```json
{
    "status": "success",
    "data": [
        {
            "day": "sunday",
            "time_windows": [
                {
                    "start_time": "08:00",
                    "end_time": "10:00"
                },
                {
                    "start_time": "12:00",
                    "end_time": "20:00"
                }
            ]
        }
    ]
}
```

If schedule was not found, invalid request message is returned:
```json
{
    "status": "fail",
    "data": {
        "message": "Schedule not found"
    }
}
```

### Get free terms
Used to fetch schedule free terms.

Route: /api/schedule/{scheduleId}/free_terms/{date}

Request Method: GET

Authorization: not required

Optional query parameters:
- range - determines how many days starting from date are checked for free terms, must be integer from 1 to 7 (1 by default).
For example request "/api/schedule/1/free_terms/2023-05-20?range=7" will fetch free terms from seven days starting from 2023-05-20.

#### Basic Request Example:

Route: /api/schedule/1/free_terms/2023-05-14

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "2023-05-14": [
            {
                "start_time": "08:00",
                "end_time": "10:00"
            },
            {
                "start_time": "12:00",
                "end_time": "15:00"
            },
            {
                "start_time": "16:00",
                "end_time": "20:00"
            }
        ]
    }
}
```

#### Request With Range Parameter Example:

Route: /api/schedule/1/free_terms/2023-05-14?range=7

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "2023-05-14": [
            {
                "start_time": "08:00",
                "end_time": "10:00"
            },
            {
                "start_time": "12:00",
                "end_time": "15:00"
            },
            {
                "start_time": "16:00",
                "end_time": "20:00"
            }
        ],
        "2023-05-15": [],
        "2023-05-16": [
            {
                "start_time": "12:00",
                "end_time": "17:00"
            }
        ],
        "2023-05-17": [],
        "2023-05-18": [
            {
                "start_time": "08:00",
                "end_time": "10:00"
            },
            {
                "start_time": "15:00",
                "end_time": "20:00"
            }
        ],
        "2023-05-19": [
            {
                "start_time": "08:00",
                "end_time": "18:00"
            }
        ],
        "2023-05-20": []
    }
}
```

If schedule was not found, invalid request message is returned:
```json
{
    "status": "fail",
    "data": {
        "message": "Schedule not found"
    }
}
```

### Get reservations
Used to fetch schedule reservations.

Route: /api/schedule/{scheduleId}/reservations/{date}

Request Method: GET

Authorization: organization admin or member assigned to schedule

Optional query parameters:
- range - determines how many days starting from date are checked for reservations, must be integer from 1 to 7 (1 by default).
For example request "/api/schedule/1/reservations/2023-05-20?range=7" will fetch reservations from seven days starting from 2023-05-20.
- verified - used to fetch reservations with specific verified state
    - when verified=1 only verified reservations will be returned
    - when verified=0 only not verified reservations will be returned
- confirmed - used to fetch reservations with specific confirmed state
    - when confirmed=1 only confirmed reservations will be returned
    - when confirmed=0 only not confirmed reservations will be returned

#### Basic Request Example:

Route: /api/schedule/1/reservations/2023-05-12

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "2023-05-12": [
            {
                "id": 1,
                "email": "test@test.com",
                "phone_number": "141416153",
                "service": {
                    "id": 1,
                    "name": "TestService1",
                    "estimated_price": "30 PLN"
                },
                "time_window": {
                    "start_time": "08:00",
                    "end_time": "09:00"
                },
                "verified": false,
                "confirmed": false
            }
        ]
    }
}
```

#### Request With Range Parameter Example:

Route: /api/schedule/1/reservations/2023-05-12?range=4

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "2023-05-12": [
            {
                "id": 1,
                "email": "test@test.com",
                "phone_number": "141416153",
                "service": {
                    "id": 1,
                    "name": "TestService1",
                    "estimated_price": "30 PLN"
                },
                "time_window": {
                    "start_time": "08:00",
                    "end_time": "09:00"
                },
                "verified": false,
                "confirmed": false
            }
        ],
        "2023-05-13": [],
        "2023-05-14": [
            {
                "id": 2,
                "email": "test@test.com",
                "phone_number": "141416153",
                "service": {
                    "id": 1,
                    "name": "TestService1",
                    "estimated_price": "30 PLN"
                },
                "time_window": {
                    "start_time": "15:00",
                    "end_time": "16:00"
                },
                "verified": false,
                "confirmed": false
            }
        ],
        "2023-05-15": []
    }
}
```

If schedule was not found, invalid request message is returned:
```json
{
    "status": "fail",
    "data": {
        "message": "Schedule not found"
    }
}
```

## Reservation

This section describes reservation related endpoints.

Endpoints:
1. [New reservation](#New-reservation)
2. [Get reservation](#Get-reservation)
3. [Modify reservation](#Modify-reservation)
4. [Delete reservation](#Delete-reservation)
5. [Confirm reservation](#Confirm-reservation)
6. [Verify reservation](#Verify-reservation)
7. [Cancel reservation](#Cancel-reservation)

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
    "schedule_id": 1,
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
    "status": "fail",
    "data": {
        "message": "Validation error",
        "errors": {
            "start_time": "Start time must be in format H:i",
            "email": "Value is not a valid email.",
            "phone_number": "Value is not valid phone number",
            "date": "Value must be in format Y-m-d"
        }
    }
}
```

If parameters format is correct, but resulting reservation time window is not available, following message will be returned:
```json
{
    "status": "fail",
    "data": {
        "message": "Validation error",
        "errors": {
            "reservation": "Reservation time window is not available"
        }
    }
}
```

#### Valid request example:

##### Request Body:
```json
{
    "schedule_id": 1,
    "date": "2023-05-12",
    "phone_number": "113242432",
    "email": "fake@fake.com",
    "service_id": 1,
    "start_time": "11:00"
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Reservation created successfully"
    }
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

Route: /api/reservation/1

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "date": "2023-05-12",
        "email": "test@test.com",
        "phone_number": "141416153",
        "time_window": {
            "start_time": "08:00",
            "end_time": "09:00"
        },
        "verified": false,
        "confirmed": true
    }
}
```

#### Request With Details Example:

Route: /api/reservation/1?details=organization,service

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "organization": {
            "id": 1,
            "name": "MyOrganization"
        },
        "date": "2023-05-12",
        "email": "test@test.com",
        "phone_number": "141416153",
        "service": {
            "id": 1,
            "name": "TestService1",
            "estimated_price": "30 PLN"
        },
        "time_window": {
            "start_time": "08:00",
            "end_time": "09:00"
        },
        "verified": false,
        "confirmed": true
    }
}
```

If reservation was not found, invalid request message is returned:
```json
{
    "status": "fail",
    "data": {
        "message": "Reservation not found"
    }
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
    "status": "fail",
    "data": {
        "message": "Invalid request",
        "errors": {
            "schedule_id": "Parameter is not allowed"
        }
    }
}
```


#### Valid request example:

##### Request Body:
```json
{
    "date": "2023-05-23",
    "start_time": "15:20"
}
```

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Reservation modified successfully"
    }
}
```

#### Additional info:
- When reservation is modified information about modification is sent to email address associated with reservation.

### Delete reservation
Used to delete reservation by organization member with appropriate credentials.

Route: /api/reservation/{reservationId}

Request Method: DELETE

Authorization: organization admin or organization member assigned to reservation schedule

#### Unauthorized Request Example:

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Access denied"
    }
}
```

#### Request With appropriate credentials:

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Reservation removed successfully"
    }
}
```

#### Additional info:
- When reservation is removed information about reservation removal is sent to email address associated with reservation.

### Confirm reservation
Used to mark reservation as confirmed by organization member with appropriate credentials.

Route: /api/reservation_confirm/{reservationId}

Request Method: POST

Authorization: organization admin or organization member assigned to reservation schedule

#### Unauthorized Request Example:

##### Response Body:
```json
{
    "status": "fail",
    "data": {
        "message": "Access denied"
    }
}
```

#### Request With appropriate credentials:

##### Response Body:
```json
{
    "status": "success",
    "data": {
        "message": "Reservation confirmed"
    }
}
```

### Verify reservation
Used to verify reservation email address.

Route: /api/reservation_verify?{query-string-with-signature}

Request Method: GET

Authorization: not required

Should only be accessed by clicking on verification link sent in email message. Returns html view with information about verification result.

### Cancel reservation
Used to cancel reservation using cancellation link sent via email.

Route: /api/reservation_cancel?{query-string-with-signature}

Request Method: GET

Authorization: not required

Should only be accessed by clicking on cancellation link sent in email message. Returns html view with information about cancellation result.
