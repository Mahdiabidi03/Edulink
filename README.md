# 🎓 **EduLink - Advanced AI-Driven Learning Management System**

**EduLink** is a modern, feature-rich Learning Management System (LMS) built with **Symfony 6.4** and **PHP 8.2**. It integrates cutting-edge technologies like **Face ID Authentication**, **Predictive Analytics**, and **Generative AI** to provide a truly personalized and secure educational experience.

---

## 🚀 **Core Features**

### 🔐 **Security & Intelligence**
*   **Role-Based Access Control (RBAC)**: Dedicated, highly-tailored dashboards for **Students**, **Tutors**, and **Admins**.
*   **Face ID Identity Management**: Next-generation biometric authentication using **face-api.js** and **TensorFlow.js**. Register your face and login securely without passwords.
*   **Standard Authentication**: Robust and secure email/password registration system with session management.

### 🤖 **AI Study Companion (Multi-Model Integration)**
*   **Hybrid RAG Chatbot**: An intelligent assistant using **Retrieval-Augmented Generation** to answer queries directly from course materials. Powered by **Google Gemini** with a high-speed fallback to **Groq API**.
*   **AI Course Recommender**: Intelligent career path analysis. Upload your **CV** or input your interests to receive personalized course suggestions.
*   **PDF Intelligence Suite**:
    *   **Automated Summarizer**: Transform long lecture notes into concise, actionable bullet points.
    *   **Smart Quiz Generator**: Generate multiple-choice quizzes automatically from any PDF to test your knowledge.
    *   **Video Script Mode**: Convert academic text into engaging, professional video scripts for creators.
*   **Sentiment & Audio Analysis**: Analyze the "vibe" of study sessions and use AI to optimize learning moods.

### 📊 **Predictive Analytics & Reporting**
*   **Dropout Risk Prediction**: Machine Learning models (implemented in **Python/Scikit-Learn**) that analyze real-time student activity (logins, task completion, engagement) to identify and alert tutors about students at risk of dropping out.
*   **Performance Metrics**: In-depth analytics for tutors including course view counts, completion rates, and resource utilization trends.

### 💰 **The EduLink Economy (Gamification)**
*   **Points-Based Reward System**: Earn points by completing courses, passing quizzes, and finishing challenges.
*   **Admin Liquidity Control**: A dedicated financial dashboard for admins to monitor the total point supply and transaction flow.
*   **Grant & Refund Terminal**: Admins can manually reward excellence or handle refunds with instant system notifications.
*   **Ledger History**: A transparent, immutable log of all transactions for every user.

---

## 🛠️ **Technology Stack**

| Layer | Technologies |
| :--- | :--- |
| **Backend** | **PHP 8.2**, **Symfony 6.4** (Framework) |
| **Frontend** | **Twig**, **Vanilla CSS** (Custom Modern Design), **JavaScript (ES6+)** |
| **Database** | **MySQL** (Managed via **Doctrine ORM**) |
| **AI / Machine Learning** | **Google Gemini API**, **Groq API**, **LangChain** (RAG), **Scikit-Learn** (Prediction Models) |
| **Biometrics** | **TensorFlow.js**, **Face-API.js** |
| **Tools** | **Composer**, **NPM**, **Docker** (Optional), **Python 3.10+** |

---

## 📦 **Installation & Setup**

### 1. **Clone the Repository**
```bash
git clone https://github.com/your-repo/edulink.git
cd edulink
```

### 2. **Install Dependencies**
```bash
composer install
# If you have frontend assets to compile
php bin/console importmap:install
```

### 3. **Configure Environment**
Duplicate `.env` and configure your local settings:
```bash
cp .env.example .env
```
Update these keys in `.env`:
```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/edulink"
GEMINI_API_KEY=your_gemini_key_here
GROQ_API_KEY=your_groq_key_here
```

### 4. **Initialize Database**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. **Set Up AI Services**
Run the Python utility to fetch necessary biometric models:
```bash
python download_models_v2.py
```

### 6. **Launch the Platform**
```bash
symfony server:start
```

---

## 🧪 **Endpoint Map & Usage**

### 👨‍🎓 **Student Experience**
*   **Dashboard**: `GET /student/dashboard` — Main hub for courses and progress.
*   **AI Tools**: `GET /student/ai-tools` — Access PDF processing, summaries, and quiz generators.
*   **Smart Wallet**: `GET /student/wallet` — Manage rewards and view transaction history.
*   **AI Recommendation**: `POST /student/recommend-courses` — Career path analysis via CV upload.
*   **Study Advisor**: `GET /api/student/study-advice` — Get personalized AI tips based on your performance.

### 👨‍🏫 **Admin & Tutor Management**
*   **Admin Portal**: `GET /admin/dashboard` — Platform overview and user management.
*   **Economy Control**: `GET /admin/economy` — Manage the points system and transaction ledgers.
*   **Predictive Alerts**: View the list of students flagged by the **Dropout Risk Model** in the User management section.

### 🔐 **Authentication Endpoints**
*   **Face ID Register**: `POST /face-id/register` — Link your biometric data to your profile.
*   **Face ID Login**: `POST /face-id/login` — Password-less entry using your webcam.

---

## 🤝 **Contribution**
We welcome contributions to make **EduLink** even better!
1. Fork the Project.
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`).
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`).
4. Push to the Branch (`git push origin feature/AmazingFeature`).
5. Open a Pull Request.

---

## 📄 **License**
Distributed under the **MIT License**. See `LICENSE` for more information.

---
*Created with ❤️ by the EduLink Team.*
