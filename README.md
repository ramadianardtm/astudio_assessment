# ASTUDIO Assessment - Ramadian Arditama Harianto
Assessment for PHP Laravel Developer position at ASTUDIO
# Setup Instruction
After finish cloning the project do these steps:
1. Run the following command to install required dependencies:
   ```sh
   composer install
   ```
2. Reset database migrations and generate seeders
   ```sh
   php artisan migrate:fresh --seed
   ```
3. Generate application key
   ```sh
   php artisan key:generate
   ```
4. Install and setup Laravel Passport
   ```sh
   php artisan passport:install
   ```
5. Start the development server
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
      "value": "Finance"
    },
    {
      "attribute_id": 2,
      "value": "2025-01-01"
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
  "name": "Project A Update",
  "status": "active",
  "attributes": [
    {
      "attribute_value_id": 9,
      "attribute_id": 1,
      "value": "Engineering"
    },
    {
      "attribute_value_id": 10,
      "attribute_id": 2,
      "value": "2025-01-01"
    },
    {
      "attribute_value_id": null,
      "attribute_id": 2,
      "value": "2026-01-01"
    }
  ],
  "removed_attributes": []
}
```
Notes:
If attribute_value_id is null, it means create a new attribute.
If removed_attributes contains an attribute_value_id, it means delete that attribute from the project.
```sh
Response:
{
  "data": "",
  "meta": {
    "message": "Successfully updated project.",
    "status_code": 200
  }
}
```
