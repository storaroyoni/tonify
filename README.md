# Tonify 🎵

<div align="center">
  <h1 style="color: #9333ea; font-family: 'Poppins', sans-serif; font-size: 4rem; font-weight: 700;">Tonify</h1>
  <p>Your Personal Music Statistics Dashboard</p>
</div>

## About Tonify 🎧

Tonify is a dynamic web application that brings your Last.fm statistics to life. It provides a beautiful, real-time interface to track your music listening habits, connect with friends, and share your musical journey.

## Features ✨

### Core Features
- **Real-time Now Playing** 
  - Live display of current track
  - Album artwork display
  - Artist and track information
  - Playback status indicator

- **Music Statistics**
  - Weekly top artists
  - Top albums overview
  - Most played tracks
  - Total scrobble count

- **Profile System**
  - Customizable user profiles
  - Profile picture upload
  - Bio customization
  - Last.fm integration

- **Social Features**
  - Friend system
  - Profile comments
  - Music activity feed
  - User interactions

## Tech Stack 🛠

### Frontend
- Blade Templates
- TailwindCSS
- Alpine.js
- JavaScript

### Backend
- Laravel 10
- PHP 8.2
- PostgreSQL
- Last.fm API

### Deployment
- Vercel
- Version Control: Git

## Getting Started 🚀

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js & NPM
- PostgreSQL
- Last.fm API Key

### Installation

1. Clone the repository
```
git clone https://github.com/yourusername/tonify.git
cd tonify
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install NPM packages:
```bash
npm install
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your `.env` file with these values:
```env
APP_KEY=your-key
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tonify
DB_USERNAME=postgres
DB_PASSWORD=your-password

LASTFM_API_KEY=your-lastfm-key
LASTFM_SECRET=your-lastfm-secret
```

7. Run migrations:
```bash
php artisan migrate
```

8. Link storage:
```bash
php artisan storage:link
```

9. Start development server:
```bash
php artisan serve
```

10. In a new terminal, run:
```bash
npm run dev
```

## API Integration 🔌

Tonify uses the Last.fm API for:
- Real-time scrobble tracking
- Music statistics
- Artist information
- Album artwork

To set up Last.fm API:
1. Create a Last.fm API account
2. Get your API key and secret
3. Add them to your environment variables

## Contributing 🤝

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Troubleshooting 🔧

Common issues:
- **Database connection errors**: Check PostgreSQL credentials
- **Storage issues**: Ensure storage:link is run
- **API errors**: Verify Last.fm API keys
- **Build failures**: Check Node.js and PHP versions

## License 📝

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support 🆘

For support:
- Open an issue in the GitHub repository
- Check existing documentation
- Contact maintainers

## Acknowledgments 👏

- Last.fm for their comprehensive API
- Laravel team for the amazing framework
- TailwindCSS for the utility-first CSS framework
- All contributors who helped build this project

---

<div align="center">
  Made with ❤️ by Yoanna Stamenova
</div>