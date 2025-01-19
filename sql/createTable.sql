-- Create applicants table
CREATE TABLE applicants (
    applicant_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    IC_number VARCHAR(50) NOT NULL,
    job_position_applied VARCHAR(255) NOT NULL,
    availability ENUM('Full-time', 'Part-time', 'Shift preferences') NOT NULL,
    asking_salary VARCHAR(50) NOT NULL,
    interview_availability TEXT,
    additional_information TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create education table
CREATE TABLE education (
    education_id INT PRIMARY KEY AUTO_INCREMENT,
    applicant_id INT NOT NULL,
    school_name VARCHAR(255) NOT NULL,
    degree_obtained VARCHAR(255) NOT NULL,
    relevant_courses_certifications TEXT,
    FOREIGN KEY (applicant_id) REFERENCES applicants(applicant_id) ON DELETE CASCADE
);

-- Create employment_history table
CREATE TABLE employment_history (
    employment_id INT PRIMARY KEY AUTO_INCREMENT,
    applicant_id INT NOT NULL,
    previous_employer VARCHAR(255) NOT NULL,
    job_title VARCHAR(255) NOT NULL,
    employment_duration VARCHAR(100) NOT NULL,
    reason_for_leaving TEXT,
    FOREIGN KEY (applicant_id) REFERENCES applicants(applicant_id) ON DELETE CASCADE
);

-- Create skills_qualifications table
CREATE TABLE skills_qualifications (
    skill_id INT PRIMARY KEY AUTO_INCREMENT,
    applicant_id INT NOT NULL,
    skill VARCHAR(255) NOT NULL,
    certification_license VARCHAR(255),
    FOREIGN KEY (applicant_id) REFERENCES applicants(applicant_id) ON DELETE CASCADE
);

-- Create references table
CREATE TABLE `references` (
    reference_id INT PRIMARY KEY AUTO_INCREMENT,
    applicant_id INT NOT NULL,
    reference_name VARCHAR(255) NOT NULL,
    reference_contact TEXT NOT NULL,
    relationship VARCHAR(100) NOT NULL,
    duration_of_relationship VARCHAR(100) NOT NULL,
    FOREIGN KEY (applicant_id) REFERENCES applicants(applicant_id) ON DELETE CASCADE
);

-- Create admin users table for authentication
CREATE TABLE admin_users (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);