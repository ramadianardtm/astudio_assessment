# ASTUDIO Assessment - Ramadian Arditama Harianto
Assessment for PHP Laravel Developer position at ASTUDIO.
# Setup Instruction
After finish cloning the project do these steps:
1. Run the following command to install required dependencies:
   ```sh
   composer install
   ```
2. Create .env file (if not available), copy and paste the .env.example and change the DB_DATABASE with your actual database.
3. Reset database migrations and generate seeders
   ```sh
   php artisan migrate --seed

   Note:
   - Use --seed to run seeder to automatically create data based on configured seeder
   - Make sure to match the DB_DATABASE in your .env with your actual database 
   ```
4. Generate application key
   ```sh
   php artisan key:generate
   ```
5. Install and setup Laravel Passport
   ```sh
   php artisan passport:install
   ```
6. Start the development server
   ```sh
   php artisan serve
   ```
# API Documentation

## Base URL
All API endpoints are prefixed with `/v1`.  
Example:  
https://yourdomain.com/api/v1


---

## Authentication APIs  
| Method  | Endpoint                 | Description                                   |
|---------|--------------------------|-----------------------------------------------|
| `POST`  | `/register`              | Register a new user                          |
| `POST`  | `/login`                 | Authenticate user and return a token         |
| `POST`  | `/logout`                | Logout user and revoke token                 |
| `POST`  | `/change-password`       | Change user's password                       |
| `DELETE`| `/delete-account`        | Delete the authenticated user's account      |

---

## User APIs (Requires Authentication)  
| Method  | Endpoint         | Description                            |
|---------|----------------|----------------------------------------|
| `PUT`   | `/update-profile` | Update the authenticated user's profile |
| `GET`   | `/user/{user_id}`    | Get details of a user by ID           |
| `GET`   | `/users`        | Get a list of all users               |

---

## Project APIs (Requires Authentication)  
| Method  | Endpoint                              | Description                                                      |
|---------|--------------------------------------|-------------------------------------------------------------------|
| `GET`   | `/projects`                         | Get a list of all projects                                         |
| `GET`   | `/project/{project_id}`                     | Get details of a project by ID                                     |
| `POST`  | `/create-project`                   | Create a new project and create attribute value                    |
| `PUT`   | `/update-project/{project_id}`              | Update project details by ID and update or create attribute value |
| `DELETE`| `/delete-project/{project_id}`              | Delete a project by ID                                             |
| `POST`  | `/project/{project_id}/assign-users`        | Assign users to a project                                          |
| `DELETE`| `/projects/{project_id}/users/{user_id}/unassign` | Unassign a user from a project                         |

---

## Timesheet APIs (Requires Authentication)  
| Method  | Endpoint                         | Description                        |
|---------|----------------------------------|------------------------------------|
| `POST`  | `/timesheets/log`               | Input timesheet log for a project      |
| `GET`   | `/timesheets`                   | Get all logged timesheets         |
| `PUT`   | `/update-timesheet/{timesheet_id}` | Update a specific timesheet entry |
| `DELETE`| `/delete-timesheet/{timesheet_id}` | Delete a specific timesheet entry |

---

## Attribute APIs (Requires Authentication)  
| Method  | Endpoint               | Description                      |
|---------|------------------------|----------------------------------|
| `GET`   | `/attributes`          | Get a list of all attributes    |
| `GET`   | `/attribute/{attribute_id}`      | Get details of a specific attribute |
| `POST`  | `/create-attribute`    | Create a new attribute          |
| `PUT`   | `/update-attribute/{attribute_id}` | Update an existing attribute    |
| `DELETE`| `/delete-attribute/{attribute_id}` | Delete an attribute by ID       |

---

## Authentication  
Most endpoints require authentication using **Bearer Token** in the `Authorization` header:
Authorization: Bearer YOUR_ACCESS_TOKEN


---

## General API Notes
- Ensure to send the Authorization header with the Bearer YOUR_ACCESS_TOKEN for authenticated requests.
- {project_id} and {user_id} in endpoints should be replaced with actual IDs.
- The API responses follow a standard structure with "meta" containing "message" and "status_code".
- Created using PHP 8.1.25

## Test Credentials

Use the following credentials to test the application:
### User Account
- **Email:** ramadianardtm@gmail.com
- **Password:** password123

## Postman API Collection
Use this link to download the Postman API Collection : https://drive.google.com/drive/folders/18l20pjpGp7flnfW7B8UT5Q5UIErk8d1S?usp=sharing

## Example Requests  

### Register  
#### Request:  
```sh
POST https://yourdomain.com/api/v1/register

Payload:
{
  "first_name": "user first name",
  "last_name": "user last name",
  "email": "user@example.com",
  "password": "yourpassword"
}


Response:
{
  "data": {
    "user": {
        "id": 1,
        "first_name": "Ramadian",
        "last_name": "Arditama",
        "email": "ramadianardtm@gmail.com"
    },
   "token": "YOUR_ACCESS_TOKEN",
  "meta": {
    "message": "Successfully create user.",
    "status_code": 200
  }
}
```
After a successful register, you can go to login API using the registered user email and password. token in the response is optional for auto logged in after registration.

### Login  
#### Request:  
```sh
POST https://yourdomain.com/api/v1/login

Payload:
{
  "email": "user@example.com",
  "password": "yourpassword"
}


Response:
{
  "data": {
    "id": 1,
    "first_name": "Ramadian",
    "last_name": "Arditama",
    "email": "ramadianardtm@gmail.com"
  },
  "auth_token": "YOUR_ACCESS_TOKEN",
  "meta": {
    "message": "Login successful.",
    "status_code": 200
  }
}
```
After a successful login, use the auth_token in the Authorization header for all authenticated requests.

### Update Profile  
```sh
Endpoint:
POST https://yourdomain.com/api/v1/change-password

Payload:
{
  "old_password": "yourOldPassword",
  "new_password": "yourNewPassword"
}

Response:
{
  "data": "",
  "meta": {
    "message": "Password changed successfully.",
    "status_code": 200
  }
}

Notes:
- The old_password must match the user's current password before the new password is accepted.
```
## Update Profile
```sh
Endpoint:
PUT /api/v1/update-profile

Payload:
{
  "first_name": "Ramadian Update",
  "last_name": "Arditama",
  "email": "ramadianardtm@gmail.com"
}

Response:
{
  "data": "",
  "meta": {
    "message": "Update profile successful.",
    "status_code": 200
  }
}
Notes:
- Only the authenticated user can update their own profile.
- The email field must remain unique but does not require an update.
```
## Get User Detail
```sh
Endpoint:
GET /api/v1/user/{user_id}

Response:
{
  "data": {
    "id": 1,
    "first_name": "Ramadian Update",
    "last_name": "Arditama A",
    "email": "ramadianardtm@gmail.com",
    "projects": [
      {
        "id": 16,
        "name": "Project A",
        "status": "active",
        "attributes": [
          { "id": 9, "attribute_id": 1, "project_id": 16, "value": "Engineering" },
          { "id": 10, "attribute_id": 2, "project_id": 16, "value": "2025-01-01" },
          { "id": 12, "attribute_id": 3, "project_id": 16, "value": "2026-01-01" }
        ],
        "timesheets": [
          { "id": 1, "task_name": "Survey Location", "date": "2025-01-01", "hours": "1" },
          { "id": 2, "task_name": "Survey Location 2", "date": "2025-01-02", "hours": "1" }
        ]
      }
  "meta": {
    "message": "Successfully get user.",
    "status_code": 200
  }
}
```
## Get All Users
```sh
Endpoint:
GET /api/v1/users

Response:
{
  "data": [
    {
      "id": 1,
      "first_name": "Ramadian Update",
      "last_name": "Arditama A",
      "email": "ramadianardtm@gmail.com",
      "projects": []
    },
    {
      "id": 3,
      "first_name": "Arya",
      "last_name": "Arditama",
      "email": "ramadianardtm3@gmail.com",
      "projects": [
          {
            "id": 16,
            "name": "Project A",
            "status": "active",
            "attributes": [
              { "id": 9, "attribute_id": 1, "project_id": 16, "value": "Engineering" },
              { "id": 10, "attribute_id": 2, "project_id": 16, "value": "2025-01-01" },
              { "id": 12, "attribute_id": 3, "project_id": 16, "value": "2026-01-01" }
            ],
            "timesheets": [
              { "id": 1, "task_name": "Survey Location", "date": "2025-01-01", "hours": "1" },
              { "id": 2, "task_name": "Survey Location 2", "date": "2025-01-02", "hours": "1" }
            ]
        }
    ]
    },
  ],
  "meta": {
    "message": "Successfully get users.",
    "status_code": 200
  }
}
Notes:
- Retrieves a list of all users along with their assigned projects and timesheets.
```
# Attributes API
## Create Attribute
```sh
Endpoint:
POST https://yourdomain.com/api/v1/create-attribute

Payload:
{
  "name": "Attribute Name",
  "type": "Attribute Type"
}

Response:
{
  "data": "",
  "meta": {
    "message": "Successfully created attributes.",
    "status_code": 200
  }
}
```

# Project API
## Create Project
```sh
Endpoint:
POST https://yourdomain.com/api/v1/create-project

Payload:
{
  "name": "Project A",
  "status": "active",
  "attributes": [
    {
      "attribute_id": 1,
      "value": "Engineering"
    },
    {
      "attribute_id": 2,
      "value": "2025-01-01"
    },
    {
      "attribute_id": 3,
      "value": "2026-01-01"
    }
  ]
}

Response:
{
  "data": "",
  "meta": {
    "message": "Successfully created project.",
    "status_code": 200
  }
}
```
## Update Project
```sh
Endpoint:
POST https://yourdomain.com/api/v1/update-project/{project_id}

Payload:
{
  "name": "Project A",
  "status": "active",
  "attributes": [
    {
      "attribute_value_id": 1,
      "attribute_id": 1,
      "value": "Engineering"
    },
    {
      "attribute_value_id": 2,
      "attribute_id": 2,
      "value": "2025-01-01"
    },
    {
      "attribute_value_id": null,
      "attribute_id": 3,
      "value": "2026-01-01"
    }
  ],
  "removed_attributes": []
}

Notes:
If attribute_value_id is null, it means create a new attribute.
If removed_attributes contains an attribute_value_id, it means delete that attribute from the project.

Response:
{
  "data": "",
  "meta": {
    "message": "Successfully updated project.",
    "status_code": 200
  }
}
```
## Get Project
```sh
Example Request (Without Filters):
GET https://yourdomain.com/api/v1/projects

Example Request (With Filters):
GET http://yourdomain.com/api/v1/projects?filters[name][LIKE]=Project A&filters[start_date][>]=2024

Response:

{
  "data": [
    {
      "id": 1,
      "name": "Project A",
      "status": "active",
      "attributes": {
        "department": "Engineering",
        "start_date": "2025-01-01",
        "end_date": "2026-01-01"
      },
      "assigned_users": [
        {
          "id": 1,
          "first_name": "Ramadian",
          "last_name": "Arditama",
          "email": "ramadianardtm@gmail.com",
          "timesheets": [
            {
              "id": 1,
              "task_name": "Survey Location",
              "date": "2025-01-01",
              "hours": "40"
            },
            {
              "id": 2,
              "task_name": "Survey Location",
              "date": "2025-01-01",
              "hours": "24"
            }
          ]
        }
      ]
    }
  ],
  "meta": {
    "message": "Successfully retrieved projects.",
    "status_code": 200
  }
}
```
## Delete Project
```sh
Endpoint:
DELETE /api/v1/delete-project/{project_id}

Example Request:
DELETE https://yourdomain.com/api/v1/delete-project/1

{
  "data": "",
  "meta": {
    "message": "Successfully deleted project.",
    "status_code": 200
  }
}
```
## Assign User to Project
```sh
### **Assign Users to a Project**
Endpoint: 
`POST /api/v1/project/{project_id}/assign-users`

Payload:
{
  "user_ids": [1]
}

Response:
{
  "data": "",
  "meta": {
    "message": "Successfully assigned project.",
    "status_code": 200
  }
}
```

## Unassign User from a Project
```sh
### **Unassign User from a Project**
Endpoint:
DELETE /api/v1/projects/{project_id}/users/{user_id}/unassign

Example Request:
DELETE https://yourdomain.com/api/v1/projects/1/users/1/unassign

Response:
{
  "data": "",
  "meta": {
    "message": "User unassigned from project and timesheets deleted.",
    "status_code": 200
  }
}
Notes:

This API removes a user from the given project.
All timesheets related to this user in the project will also be deleted.
```
## Log Timesheet
```sh
Endpoint:
POST /api/v1/timesheets/log

Payload:
{
  "task_name": "Survey Location",
  "date": "2025-01-01",
  "hours": 40,
  "project_id": 1
}

Response:
{
  "data": "",
  "meta": {
    "message": "Timesheet logged successfully.",
    "status_code": 200
  }
}

Notes:
- The user_id is automatically taken from the authenticated user.
- project_id must be provided to log the timesheet under the correct project.
```

## Get All Timesheets
```sh
Endpoint:
GET /api/v1/timesheets

Response:
{
  "data": [
    {
      "id": 1,
      "task_name": "Survey Location",
      "date": "2025-01-01",
      "hours": "40",
      "project": {
        "id": 1,
        "name": "Project A",
        "status": "active",
        "users": [
          {
            "id": 1,
            "first_name": "Ramadian",
            "last_name": "Arditama",
            "email": "ramadianardtm@gmail.com"
          }
        ]
      }
    }
  ],
  "meta": {
    "message": "Successfully get timesheets.",
    "status_code": 200
  }
}

Notes:
- Fetches all logged timesheets with project and user details.
```

## Update Timesheet
```sh
Endpoint:
PUT /api/v1/update-timesheet/{timesheet_id}

Payload:
{
  "task_name": "Updated Task",
  "date": "2025-02-01",
  "hours": 30
}

Response:
{
  "data": "",
  "meta": {
    "message": "Successfully update timesheet.",
    "status_code": 200
  }
}
```
## Delete Timesheet
```sh
Endpoint:
DELETE /api/v1/delete-timesheet/{timesheet_id}

Response:
{
  "data": "",
  "meta": {
    "message": "Successfully delete timesheet.",
    "status_code": 200
  }
}
```
