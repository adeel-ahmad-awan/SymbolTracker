# SymbolTracker

## Description

This project is a Symfony application for managing company symbols and displaying their historical quotes.

## Requirements

- PHP version 8.1 (minimum required version)

## Setup

- Clone the repository:

`git clone git@github.com:adeel-ahmad-awan/synfonyTest.git`

- Change directory
`cd synfonyTest/`

- Add your database credentials:

Edit the `.env` file and update the database connection details with your credentials.
Also add your api keys and mailer credentials in `.env` file

- Install dependencies:

`composer install`

- Create the database:

`php bin/console doctrine:database:create`

- Update the database schema:

`php bin/console doctrine:schema:create`

- Run the command to add data:

`php bin/console app:sync-company-symbol`

This command will populate the database with company symbols.

- Start the Symfony server:

`symfony server:start`

You should now be able to access the application in your browser at `http://127.0.0.1:8000`.

## Usage

- Access the homepage by visiting `http://127.0.0.1:8000`. If the company symbol table is empty, you will be redirected to run the command to add data.
- Fill in the form on the homepage to get historical quotes for a specific company symbol.
- The processed data will be displayed on the show page at `http://127.0.0.1:8000/show`.
- An email will be sent with the processed data (email service configuration required).
- Use the provided command `app:sync-company-symbol` to sync the company symbols at any time.

## Testing

The project includes a suite of automated tests to ensure its functionality. To run the tests, follow these steps:

1. Make sure the project dependencies are installed:
2. Add testing Database credentials in `.env.test`
3. Run the tests using the following command:
`symfony php bin/phpunit`

The tests will be executed, and the results will be displayed in the terminal.
