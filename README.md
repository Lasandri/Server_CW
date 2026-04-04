# Alumni Influencers Platform

A web-based API platform for the University of Eastminster alumni engagement system, featuring blind bidding for daily featured alumni slots and comprehensive API access.

## Features

- **Alumni Registration & Authentication**
  - Email-based registration with university domain validation
  - Secure email verification system
  - Password reset functionality with secure tokens
  - Session management with timeout protection

- **Complete Alumni Profiles**
  - Personal information and biography
  - Educational history (degrees, certifications, licences)
  - Professional courses and employment history
  - Profile image upload
  - LinkedIn integration

- **Blind Bidding System**
  - Place bids without seeing competitor amounts
  - Real-time win/lose status feedback
  - Monthly limit enforcement (3 featured slots/month + event bonus)
  - Automated daily winner selection at midnight
  - Email notifications

- **Developer API**
  - Public endpoints for alumni data access
  - Secure bearer token authentication
  - API key management with usage statistics
  - Rate limiting and security tokens
  - Comprehensive Swagger documentation

## Tech Stack

- **Backend (Web):** CodeIgniter 3 (PHP) - Web forms, sessions, profile management
- **Backend (API):** Node.js (Express) - REST API, bidding system, Swagger docs
- **Database:** MySQL 5.7+
- **Security:** Bcrypt, HTTPS, CSRF protection, rate limiting, Helmet.js
- **Documentation:** Swagger/OpenAPI 3.0

## Installation

### Prerequisites

- PHP 7.4+
- Node.js 14+
- MySQL 5.7+
- Composer
- npm

### 1. Clone Repository

```bash
git clone <your-repo-url>
cd CW