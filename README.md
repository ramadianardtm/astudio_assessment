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
| `GET`   | `/user/{id}`    | Get details of a user by ID           |
| `GET`   | `/users`        | Get a list of all users               |

---

## Project APIs (Requires Authentication)  
| Method  | Endpoint                              | Description                                |
|---------|--------------------------------------|--------------------------------------------|
| `GET`   | `/projects`                         | Get a list of all projects                |
| `GET`   | `/project/{id}`                     | Get details of a project by ID            |
| `POST`  | `/create-project`                   | Create a new project                      |
| `PUT`   | `/update-project/{id}`              | Update project details by ID              |
| `DELETE`| `/delete-project/{id}`              | Delete a project by ID                    |
| `POST`  | `/project/{id}/assign-users`        | Assign users to a project                 |
| `DELETE`| `/projects/{projectId}/users/{userId}/unassign` | Unassign a user from a project |

---

## Timesheet APIs (Requires Authentication)  
| Method  | Endpoint                         | Description                        |
|---------|----------------------------------|------------------------------------|
| `POST`  | `/timesheets/log`               | Log work hours for a project      |
| `GET`   | `/timesheets`                   | Get all logged timesheets         |
| `PUT`   | `/update-timesheet/{timesheet_id}` | Update a specific timesheet entry |
| `DELETE`| `/delete-timesheet/{timesheet_id}` | Delete a specific timesheet entry |

---

## Attribute APIs (Requires Authentication)  
| Method  | Endpoint               | Description                      |
|---------|------------------------|----------------------------------|
| `GET`   | `/attributes`          | Get a list of all attributes    |
| `GET`   | `/attribute/{id}`      | Get details of a specific attribute |
| `POST`  | `/create-attribute`    | Create a new attribute          |
| `PUT`   | `/update-attribute/{id}` | Update an existing attribute    |
| `DELETE`| `/delete-attribute/{id}` | Delete an attribute by ID       |

---

## Authentication  
Most endpoints require authentication using **Bearer Token** in the `Authorization` header:  
