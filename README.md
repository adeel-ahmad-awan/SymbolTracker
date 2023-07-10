# Symfony test

## Description

This project is a Symfony application for managing company symbols and displaying their historical quotes.

## Requirements

- PHP version 8.1 (minimum required version)

## Setup

1. Clone the repository:

`git clone https://github.com/your/repository.git`

2. Install dependencies:

`composer install`

3. Add your database credentials:

Edit the `.env` file and update the database connection details with your credentials.
Also add your api keys and mailer credentials in `.env` file

4. Create the database:

`php bin/console doctrine:database:create`

5. Update the database schema:

`php bin/console doctrine:schema:create`

6. Run the command to add data:

`php bin/console app:sync-company-symbol`

This command will populate the database with company symbols.

7. Start the Symfony server:

`symfony server:start`

You should now be able to access the application in your browser at `http://127.0.0.1:8000`.

## Usage

- Access the homepage by visiting `http://127.0.0.1:8000`. If the company symbol table is empty, you will be redirected to run the command to add data.
- Fill in the form on the homepage to get historical quotes for a specific company symbol.
- The processed data will be displayed on the show page at `http://127.0.0.1:8000/show`.
- An email will be sent with the processed data (email service configuration required).
- Use the provided command `app:sync-company-symbol` to sync the company symbols at any time.

