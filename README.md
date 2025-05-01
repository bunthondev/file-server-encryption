# Secure File Server with Encryption

A secure file storage system built with Laravel that provides end-to-end encryption for uploaded files. Files are encrypted at rest using AES-256-CBC encryption with a custom encryption key derived from a secret word and the application key.

## Features

-   🔒 End-to-end file encryption using AES-256-CBC
-   📁 Organized file storage using buckets
-   🔑 Custom encryption key generation
-   👤 User-based access control
-   📤 Secure file upload and download
-   🔍 File preview support
-   🗑️ Secure file deletion
-   🔐 Sanctum authentication

## Security Features

-   Files are encrypted before storage using AES-256-CBC
-   Unique initialization vector (IV) for each file
-   Custom encryption key derived from:
    -   User-defined secret word (ENCRYPTION_SECRET)
    -   Application key (APP_KEY)
-   Encrypted filenames with preserved extensions
-   Secure file paths using bucket names
-   User-based access control
-   API token authentication

## Requirements

-   PHP 8.1 or higher
-   Laravel 10.x
-   SQLite/MySQL/PostgreSQL
-   OpenSSL PHP extension

## Installation

1. Clone the repository:

```bash
git clone https://github.com/yourusername/file-server-encryption.git
cd file-server-encryption
```

2. Install dependencies:

```bash
composer install
```

3. Copy environment file:

```bash
cp .env.example .env
```

4. Generate application key:

```bash
php artisan key:generate
```

5. Set up encryption secret in `.env`:

```env
ENCRYPTION_SECRET=your-secret-word-here
```

6. Run migrations:

```bash
php artisan migrate
```

7. Start the development server:

```bash
php artisan serve
```

## API Endpoints

### Upload File

```bash
POST /api/files
Content-Type: multipart/form-data
Authorization: Bearer {token}

Parameters:
- file: The file to upload
- bucket: The bucket name to store the file in
```

### Download File

```bash
GET /api/files/{bucket}/{filename}/download
Authorization: Bearer {token}
```

### View File

```bash
GET /api/files/{bucket}/{filename}/view
Authorization: Bearer {token}
```

### List Files in Bucket

```bash
GET /api/files?bucket={bucket_name}
Authorization: Bearer {token}
```

### Delete File

```bash
DELETE /api/files/{bucket}/{filename}
Authorization: Bearer {token}
```

## File Storage Structure

Files are stored in the following structure:

```
storage/app/
  ├── bucket1/
  │   ├── encrypted_file1.pdf
  │   └── encrypted_file2.jpg
  └── bucket2/
      └── encrypted_file3.txt
```

## Security Considerations

1. Keep your `ENCRYPTION_SECRET` secure and never share it
2. Regularly rotate your `APP_KEY` and `ENCRYPTION_SECRET`
3. Use HTTPS in production
4. Implement rate limiting for API endpoints
5. Regularly backup your database and encrypted files
6. Monitor file access logs

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

-   Laravel Framework
-   OpenSSL
-   PHP
