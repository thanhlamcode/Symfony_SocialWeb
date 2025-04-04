# Symfony_SocialWeb

A feature-rich social networking platform that allows users to connect, chat in real-time, manage friendships, and share posts. Built using Symfony for the backend, WebSockets for real-time communication, and TailwindCSS for modern styling.

## 🚀 Features

- User authentication and registration (including OAuth2 with Google)
- Real-time chat using WebSockets
- Friend management system
- Posting and commenting functionality
- User profiles with editable data
- News and dashboard interfaces

## 🛠️ Technologies Used

- **Symfony** (Backend framework)
- **WebSockets** (Real-time communication)
- **Doctrine ORM** (Database management)
- **Twig** (Templating engine)
- **Webpack Encore** (Asset management)
- **TailwindCSS** (Modern styling framework)

## 📁 Project Structure

```bash
thanhlamcode-symfony_socialweb/
├── assets/                  # JS, CSS, images, icons
├── bin/                     # Symfony binary
├── config/                  # Symfony config files
├── public/                  # Publicly accessible files (entry point)
├── src/                     # PHP source code
│   ├── Controller/          # Symfony controllers
│   ├── Entity/              # Doctrine entities
│   ├── Form/                # Symfony form types
│   ├── Message/             # Message classes
│   ├── MessageHandler/      # Handlers for Symfony Messenger
│   ├── Repository/          # Doctrine repositories
│   ├── Security/            # Custom authentication logic
│   └── Service/             # Business logic services
├── templates/               # Twig templates
├── composer.json            # PHP dependencies
├── importmap.php            # Import map for Symfony assets
├── package.json             # JS dependencies
├── Procfile                 # Deployment process file (e.g., for Heroku)
├── server.js                # WebSocket server
├── symfony.lock             # Symfony lock file
├── tailwind.config.js       # TailwindCSS config
├── webpack.config.js        # Webpack config
```

## 🖥 Running Locally

Run the project using Symfony CLI:

```bash
symfony server:start
```

Then access the app at: `http://localhost:8000`

## 🔐 Authentication

Supports traditional login and Google OAuth2 login.

## 🎨 Styling

TailwindCSS and Webpack Encore are used for styling and asset management.

## 📜 License

MIT

## 👨‍💻 Author

[thanhlamcode](https://github.com/thanhlamcode)

---
*Feel free to contribute or fork this project!*

