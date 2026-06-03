# SnipVault

A simple PHP and MySQL project for storing and managing code snippets.

I built this project to keep useful code snippets in one place instead of searching through old files and folders. Snippets can be organized by programming language and tags, making them easier to find later.

## Features

* Add new code snippets
* View saved snippets
* Edit existing snippets
* Delete snippets
* Store snippets with a title and description
* Categorize snippets by programming language
* Add tags to snippets
* Search for snippets
* Syntax highlighting using Prism.js
* Upload files related to a snippet

## Built With

* PHP
* MySQL
* HTML
* CSS
* JavaScript
* Prism.js

## Project Structure

```text
SnipVault/
│
├── config/
│   └── db.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── prism/
│
├── uploads/
│
├── index.php
├── create.php
├── view.php
├── edit.php
├── delete.php
│
└── snippet_vault.sql
```

## How to Run

### 1. Clone the repository

```bash
git clone https://github.com/your-username/snipvault.git
```

### 2. Create the database

Create a database named:

```sql
snippet_vault
```

Import the file:

```text
snippet_vault.sql
```

### 3. Configure the database

Open:

```php
config/db.php
```

Update the database credentials if needed:

```php
$host = 'localhost';
$db   = 'snippet_vault';
$user = 'root';
$pass = '';
```

### 4. Run the project

Place the project inside your XAMPP `htdocs` folder and start Apache and MySQL.

Open:

```text
http://localhost/snipvault
```

## Why I Made This

As I learn programming, I often come across useful pieces of code that I want to save for future use. This project helps me keep those snippets organized and easily accessible.

Plus this is like an offline github for me to access whenever i can't access github due to the days where i have no wifi or electricity.

## Future Improvements

Some features I may add in the future:

* User login system
* Favorite snippets
* Dark mode
* Export snippets
* Better search filters
* Markdown support

## Screenshots

You can add screenshots here later.

```markdown
![Home Page](screenshots/home.png)
```

## Author

Created by Sarthak as a learning project using PHP and MySQL.
