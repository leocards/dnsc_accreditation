DNSC Accreditation is a document management system for accreditation. Developed to streamline the the accreditation process of the Davao del Norte State College.

![Screenshot 2023-05-02 165356](https://github.com/leocards/dnsc_accreditation/assets/72657565/e506d906-d24a-4f9b-b8e3-61455bb3cbc9)

The system features:

1. User Account Management
2. Management of Accreditation Instrument for each program
3. Management of Task force Members
4. Management of Documents
5. Notification
6. In-app chat
7. Accreditaion Survey

## Installation

1. Close the directory
2. Navigate to the project directory
3. Run composer install
4. npm install
5. php artisan migrate ( after running this command, type yes to create the database)
6. php artisan seed

## Usage

1. php artisan serve
2. php artisan websockets:serve
3. npm run dev
4. create a .env file and copy the contents from .env.example

For user access you can navigate to database > seeders > UserSeeder.php
