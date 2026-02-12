<<<<<<< HEAD
# EduLink - Advanced Learning Management System

EduLink is a modern, feature-rich Learning Management System (LMS) built with **Symfony 7** and **PHP 8.2**. It integrates cutting-edge technologies like **Face ID Authentication** and **Generative AI** to enhance the learning experience.

## 🚀 Key Features

### 🔐 Security & Authentication
*   **Role-Based Access Control**: Separate dashboards for Students, Tutors, and Admins.
*   **Face ID Login**: Secure, password-less login using biometric face recognition (powered by `face-api.js`).
*   **Standard Auth**: Robust email/password registration and login system.

### 🤖 AI Study Companion (Powered by Gemini)
*   **PDF Summarizer**: Upload lecture notes and get instant bullet-point summaries.
*   **Quiz Generator**: Automatically create multiple-choice quizzes from any PDF document.
*   **Video Script Mode**: Convert complex text into engaging video scripts for content creation.

### 💰 Economy & Gamification
*   **Points System**: Students earn points for completing courses and challenges.
*   **Admin Economy Dashboard**:
    *   Real-time liquidity and transaction monitoring.
    *   **Grant/Refund Terminal**: Admins can manually send points to users with notifications.
*   **Ledger History**: Transparent transaction log for all users.

### 🔔 Real-Time Notifications
*   **Smart Widget**: Top-bar notification bell with unread count badges.
*   **Event Triggers**: System alerts for received points, course updates, and admin messages.

## 🛠️ Technology Stack
*   **Backend**: PHP 8.2, Symfony 7.4
*   **Frontend**: Twig, Vanilla CSS (Modern Design), JavaScript
*   **Database**: MySQL (Doctrine ORM)
*   **AI/ML**: 
    *   Google Gemini API (Generative Content)
    *   TensorFlow.js / Face-API.js (Biometrics)

## 📦 Installation & Setup

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/your-repo/edulink.git
    cd edulink
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    ```

3.  **Configure Environment**
    *   Create a `.env` file (copy `.env.example`).
    *   Set your database credentials:
        ```env
        DATABASE_URL="mysql://root:@127.0.0.1:3306/edulink"
        ```
    *   **Crucial:** Add your Google Gemini API Key:
        ```env
        GEMINI_API_KEY=your_key_here
        ```

4.  **Database Migration**
    ```bash
    php bin/console doctrine:migrations:migrate
    ```

5.  **Install AI Models (Locally)**
    *   Run the Python script to download Face API models:
        ```bash
        python download_models_v2.py
        ```

6.  **Run the Server**
    ```bash
    symfony server:start
    # OR using PHP built-in server
    php -S localhost:8000 -t public/
    ```

## 🧪 Usage Guide

*   **Student Dashboard**: `/student/dashboard`
    *   Access courses, check wallet balance, use AI tools, and setup Face ID.
*   **Admin Dashboard**: `/admin/dashboard`
    *   Manage users, courses, and the platform economy.
*   **Face ID Setup**:
    1.  Go to Student Dashboard -> Security Settings.
    2.  Register your face.
    3.  Logout and use "Login with Face ID" on the login page.

## 🤝 Contribution
Feel free to fork this project and submit pull requests. For major changes, please open an issue first to discuss what you would like to change.

## 📄 License
[MIT](https://choosealicense.com/licenses/mit/)
=======
# edulink
>>>>>>> 0c8e64f5779048d5eddc79d9e00142bae5d883a8
