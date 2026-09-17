# RedSim - Security Analysis & Education Platform

RedSim is a comprehensive web-based platform built with Laravel that provides various cybersecurity tools, AI-powered security analysis, and interactive educational features.

## 🚀 Features

### 🛡️ Security Analysis

* **Analisis Kode (Code Analysis):** Inspect source code for vulnerabilities.
* **Analisis URL (URL Analysis):** Verify and scan URLs for malicious content. Includes domain verification.
* **Analisis ZIP (ZIP Analysis):** Scan archived files for security threats.
* **Analisis Log (Log Analysis):** Parse and analyze log files for anomalies.
* **AI-Powered Analysis:** Deep integration with AI for advanced threat detection and analysis.
* **PDF Reports:** Generate and export comprehensive security scan reports.

### 🎓 Education & Training

* **Tantangan (Challenges):** Interactive cybersecurity quizzes and challenges.
* **Ensiklopedia (Encyclopedia):** Knowledge base for cybersecurity concepts.
* **Leaderboard:** Gamified learning experience to track progress and rank users.

### 🔐 Authentication & Security

* **WhatsApp OTP:** Secure login and password reset via WhatsApp OTP.
* **Google OAuth:** Seamless single sign-on (SSO) integration.
* **User Approval System:** Manual verification process by admin for new users.

### 📊 Admin Dashboard

* **System Health Monitoring:** Keep track of the platform's status.
* **Audit Logs:** Monitor user activities and system events.
* **AI Configuration:** Manage API keys and LLM settings dynamically.
* **User & Content Management:** Manage users, challenges, and approvals.

### 🤖 AI Chat Assistant

* Integrated AI Chatbot to assist users with cybersecurity queries.
* Retrieval-Augmented Generation (RAG) debugging for administrators.

## 🛠️ Tech Stack

* **Backend:** [Laravel](https://laravel.com/) (PHP 8.3+)
* **Database:** MySQL / PostgreSQL
* **Authentication:** Laravel Socialite, Custom WhatsApp Integration

## ⚙️ Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/barbarbarox/redsim.git
   cd redsim
   ```
2. **Install dependencies:**

   ```bash
   composer install
   npm install
   ```
3. **Configure Environment:**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   *Make sure to configure your database connection and API keys (Google OAuth, WhatsApp API, AI services) in the `.env` file.*
4. **Run Migrations & Seeders:**

   ```bash
   php artisan migrate --seed
   ```
5. **Serve the Application:**

   ```bash
   php artisan serve
   npm run dev
   php artisan queue:work
   ```
   
   *Catatan: Jalankan `php artisan queue:work` di terminal terpisah agar fitur AI dan proses latar belakang lainnya dapat berjalan dengan lancar dan aman.*

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
