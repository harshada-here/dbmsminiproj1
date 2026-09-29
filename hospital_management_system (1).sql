-- =====================================================================
-- HOSPITAL MANAGEMENT SYSTEM - SQL IMPLEMENTATION
-- DBMS Project (3rd Semester)
-- Run this entire file in MySQL terminal:  mysql -u root -p < hospital_management_system.sql
-- Or paste section by section into the MySQL prompt.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. DATABASE INITIALIZATION
-- ---------------------------------------------------------------------
DROP DATABASE IF EXISTS HospitalManagementDB;
CREATE DATABASE HospitalManagementDB;
USE HospitalManagementDB;

-- ---------------------------------------------------------------------
-- 2. TABLE CREATION (DDL)
-- ---------------------------------------------------------------------

-- A. Department
CREATE TABLE DEPARTMENT (
    Department_ID   INT PRIMARY KEY,
    Department_Name VARCHAR(100) NOT NULL,
    Location        VARCHAR(100),
    Contact_Number  VARCHAR(15)
);

-- B. Doctor
CREATE TABLE DOCTOR (
    Doctor_ID       INT PRIMARY KEY,
    Department_ID   INT,
    Doctor_Name     VARCHAR(100) NOT NULL,
    Specialization  VARCHAR(100),
    Phone           VARCHAR(15),
    Email           VARCHAR(100),
    FOREIGN KEY (Department_ID) REFERENCES DEPARTMENT(Department_ID)
);

-- C. Patient
CREATE TABLE PATIENT (
    Patient_ID     INT PRIMARY KEY,
    First_Name     VARCHAR(50) NOT NULL,
    Last_Name      VARCHAR(50) NOT NULL,
    Date_Of_Birth  DATE,
    Gender         VARCHAR(10),
    Phone          VARCHAR(15),
    Email          VARCHAR(100),
    Address        TEXT,
    Blood_Group    VARCHAR(5)
);

-- D. Appointment
CREATE TABLE APPOINTMENT (
    Appointment_ID   INT PRIMARY KEY,
    Patient_ID       INT,
    Doctor_ID        INT,
    Appointment_Date DATE NOT NULL,
    Appointment_Time TIME NOT NULL,
    Status           VARCHAR(20) DEFAULT 'Scheduled',
    Reason           VARCHAR(255),
    FOREIGN KEY (Patient_ID) REFERENCES PATIENT(Patient_ID),
    FOREIGN KEY (Doctor_ID) REFERENCES DOCTOR(Doctor_ID)
);

-- E. Medical Record
CREATE TABLE MEDICAL_RECORD (
    Record_ID       INT PRIMARY KEY,
    Patient_ID      INT,
    Doctor_ID       INT,
    Appointment_ID  INT,
    Diagnosis       VARCHAR(255),
    Symptoms        TEXT,
    Treatment       TEXT,
    Record_Date     DATE NOT NULL,
    FOREIGN KEY (Patient_ID) REFERENCES PATIENT(Patient_ID),
    FOREIGN KEY (Doctor_ID) REFERENCES DOCTOR(Doctor_ID),
    FOREIGN KEY (Appointment_ID) REFERENCES APPOINTMENT(Appointment_ID)
);

-- F. Prescription
CREATE TABLE PRESCRIPTION (
    Prescription_ID   INT PRIMARY KEY,
    Record_ID         INT,
    Prescription_Date DATE NOT NULL,
    Instructions      TEXT,
    FOREIGN KEY (Record_ID) REFERENCES MEDICAL_RECORD(Record_ID)
);

-- Medicine
CREATE TABLE MEDICINE (
    Medicine_ID    INT PRIMARY KEY,
    Medicine_Name  VARCHAR(100) NOT NULL,
    Category       VARCHAR(50),
    Manufacturer   VARCHAR(100),
    Unit_Price     DECIMAL(10, 2) NOT NULL,
    Stock_Quantity INT DEFAULT 0,
    Expiry_Date    DATE
);

-- G. Prescription-Medicine junction table
CREATE TABLE PRESCRIPTION_MEDICINE (
    Prescription_ID INT,
    Medicine_ID     INT,
    Dosage          VARCHAR(50),
    Frequency       VARCHAR(50),
    Duration        VARCHAR(50),
    Quantity        INT NOT NULL,
    PRIMARY KEY (Prescription_ID, Medicine_ID),
    FOREIGN KEY (Prescription_ID) REFERENCES PRESCRIPTION(Prescription_ID),
    FOREIGN KEY (Medicine_ID) REFERENCES MEDICINE(Medicine_ID)
);

-- Bill
CREATE TABLE BILL (
    Bill_ID             INT PRIMARY KEY,
    Patient_ID          INT,
    Appointment_ID      INT,
    Bill_Date           DATE NOT NULL,
    Consultation_Charge DECIMAL(10, 2) DEFAULT 0.00,
    Medicine_Charge     DECIMAL(10, 2) DEFAULT 0.00,
    Other_Charges       DECIMAL(10, 2) DEFAULT 0.00,
    Total_Amount        DECIMAL(10, 2),
    Payment_Status      VARCHAR(20) DEFAULT 'Unpaid',
    FOREIGN KEY (Patient_ID) REFERENCES PATIENT(Patient_ID),
    FOREIGN KEY (Appointment_ID) REFERENCES APPOINTMENT(Appointment_ID)
);

-- ---------------------------------------------------------------------
-- 3. SAMPLE DATA (INSERTS) -- so queries actually return results on demo
-- ---------------------------------------------------------------------

INSERT INTO DEPARTMENT VALUES
(1, 'Cardiology', 'Block A, 2nd Floor', '011-2345001'),
(2, 'Orthopedics', 'Block B, 1st Floor', '011-2345002'),
(3, 'Pediatrics', 'Block A, 1st Floor', '011-2345003'),
(4, 'General Medicine', 'Block C, Ground Floor', '011-2345004');

INSERT INTO DOCTOR VALUES
(101, 1, 'Dr. Anil Sharma', 'Cardiologist', '9876500101', 'anil.sharma@hospital.com'),
(102, 2, 'Dr. Neha Verma', 'Orthopedic Surgeon', '9876500102', 'neha.verma@hospital.com'),
(103, 3, 'Dr. Ravi Kumar', 'Pediatrician', '9876500103', 'ravi.kumar@hospital.com'),
(104, 4, 'Dr. Sunita Rao', 'General Physician', '9876500104', 'sunita.rao@hospital.com');

INSERT INTO PATIENT VALUES
(201, 'Aditya', 'Mehta', '1990-05-14', 'Male', '9123456701', 'aditya.mehta@mail.com', 'Pune, MH', 'B+'),
(202, 'Priya', 'Singh', '1985-11-02', 'Female', '9123456702', 'priya.singh@mail.com', 'Mumbai, MH', 'O+'),
(203, 'Karan', 'Patel', '2001-02-20', 'Male', '9123456703', 'karan.patel@mail.com', 'Nagpur, MH', 'A-'),
(204, 'Sneha', 'Joshi', '1978-07-09', 'Female', '9123456704', 'sneha.joshi@mail.com', 'Pune, MH', 'AB+');

INSERT INTO APPOINTMENT VALUES
(301, 201, 101, '2026-09-10', '10:00:00', 'Completed', 'Chest pain'),
(302, 202, 102, '2026-09-11', '11:30:00', 'Completed', 'Knee pain'),
(303, 203, 103, '2026-09-12', '09:15:00', 'Scheduled', 'Fever'),
(304, 204, 104, '2026-09-12', '15:00:00', 'Cancelled', 'General checkup');

INSERT INTO MEDICAL_RECORD VALUES
(401, 201, 101, 301, 'Hypertension', 'Chest discomfort, breathlessness', 'Prescribed medication, advised diet control', '2026-09-10'),
(402, 202, 102, 302, 'Osteoarthritis', 'Joint pain, swelling', 'Physiotherapy + medication', '2026-09-11');

INSERT INTO PRESCRIPTION VALUES
(501, 401, '2026-09-10', 'Take medicines after food, follow up in 2 weeks'),
(502, 402, '2026-09-11', 'Apply ointment twice daily, avoid heavy exercise');

INSERT INTO MEDICINE VALUES
(601, 'Amlodipine', 'Cardiac', 'Cipla Ltd', 5.50, 200, '2027-06-30'),
(602, 'Paracetamol', 'Analgesic', 'Sun Pharma', 2.00, 500, '2027-12-31'),
(603, 'Diclofenac Gel', 'Anti-inflammatory', 'GSK', 45.00, 100, '2027-03-31');

INSERT INTO PRESCRIPTION_MEDICINE VALUES
(501, 601, '5mg', 'Once daily', '30 days', 30),
(501, 602, '500mg', 'Twice daily', '5 days', 10),
(502, 603, 'Apply thin layer', 'Twice daily', '15 days', 1);

INSERT INTO BILL VALUES
(701, 201, 301, '2026-09-10', 500.00, 165.00, 50.00, 715.00, 'Paid'),
(702, 202, 302, '2026-09-11', 600.00, 45.00, 0.00, 645.00, 'Pending'),
(703, 203, 303, '2026-09-12', 300.00, 0.00, 0.00, 300.00, 'Unpaid');

-- ---------------------------------------------------------------------
-- 4. CORE ANALYTICAL QUERIES (from project spec)
-- ---------------------------------------------------------------------

-- Query 1: Complete Patient Appointment Summary (Multi-table JOIN)
SELECT a.Appointment_ID,
       CONCAT(p.First_Name, ' ', p.Last_Name) AS Patient_Name,
       d.Doctor_Name,
       dept.Department_Name,
       a.Appointment_Date,
       a.Status
FROM APPOINTMENT a
JOIN PATIENT p     ON a.Patient_ID = p.Patient_ID
JOIN DOCTOR d      ON a.Doctor_ID = d.Doctor_ID
JOIN DEPARTMENT dept ON d.Department_ID = dept.Department_ID;

-- Query 2: Patient Prescription & Dosage Information
SELECT p.First_Name, p.Last_Name, m.Medicine_Name, pm.Dosage, pm.Frequency,
       pm.Duration, pr.Instructions
FROM PRESCRIPTION pr
JOIN MEDICAL_RECORD mr ON pr.Record_ID = mr.Record_ID
JOIN PATIENT p ON mr.Patient_ID = p.Patient_ID
JOIN PRESCRIPTION_MEDICINE pm ON pr.Prescription_ID = pm.Prescription_ID
JOIN MEDICINE m ON pm.Medicine_ID = m.Medicine_ID;

-- Query 3: Total Revenue Collected Per Department
SELECT dept.Department_Name, SUM(b.Total_Amount) AS Total_Revenue
FROM BILL b
JOIN APPOINTMENT a ON b.Appointment_ID = a.Appointment_ID
JOIN DOCTOR d ON a.Doctor_ID = d.Doctor_ID
JOIN DEPARTMENT dept ON d.Department_ID = dept.Department_ID
WHERE b.Payment_Status = 'Paid'
GROUP BY dept.Department_Name;

-- Query 4: Audit Pending Patient Payments (LEFT JOIN)
SELECT p.Patient_ID, CONCAT(p.First_Name, ' ', p.Last_Name) AS Patient_Name,
       b.Bill_ID, b.Total_Amount, b.Payment_Status
FROM PATIENT p
LEFT JOIN BILL b ON p.Patient_ID = b.Patient_ID AND b.Payment_Status = 'Pending';

-- ---------------------------------------------------------------------
-- 5. EXTRA QUERIES (useful additions for a stronger project demo)
-- ---------------------------------------------------------------------

-- 5a. Doctor-wise appointment count
SELECT d.Doctor_Name, COUNT(a.Appointment_ID) AS Total_Appointments
FROM DOCTOR d
LEFT JOIN APPOINTMENT a ON d.Doctor_ID = a.Doctor_ID
GROUP BY d.Doctor_Name;

-- 5b. Medicines low in stock (inventory control)
SELECT Medicine_Name, Stock_Quantity
FROM MEDICINE
WHERE Stock_Quantity < 150
ORDER BY Stock_Quantity ASC;

-- 5c. Medicines nearing expiry (within 6 months of latest bill date)
SELECT Medicine_Name, Expiry_Date
FROM MEDICINE
WHERE Expiry_Date <= DATE_ADD('2026-09-23', INTERVAL 6 MONTH);

-- 5d. Patients with more than one appointment (subquery + HAVING)
SELECT Patient_ID, COUNT(*) AS Appointment_Count
FROM APPOINTMENT
GROUP BY Patient_ID
HAVING COUNT(*) > 1;

-- 5e. Full medical history for a specific patient (parameterized example: Patient_ID = 201)
SELECT mr.Record_Date, mr.Diagnosis, mr.Symptoms, mr.Treatment, d.Doctor_Name
FROM MEDICAL_RECORD mr
JOIN DOCTOR d ON mr.Doctor_ID = d.Doctor_ID
WHERE mr.Patient_ID = 201
ORDER BY mr.Record_Date DESC;

-- 5f. Update example: mark an appointment as completed
UPDATE APPOINTMENT
SET Status = 'Completed'
WHERE Appointment_ID = 303;

-- 5g. Delete example: remove a cancelled appointment's bill reference is not needed here,
-- but shows a safe delete pattern (kept commented to avoid breaking FK demo data)
-- DELETE FROM APPOINTMENT WHERE Status = 'Cancelled';

-- 5h. View: simplifies repeated reporting query (Query 1) for reuse
CREATE VIEW VW_APPOINTMENT_SUMMARY AS
SELECT a.Appointment_ID,
       CONCAT(p.First_Name, ' ', p.Last_Name) AS Patient_Name,
       d.Doctor_Name,
       dept.Department_Name,
       a.Appointment_Date,
       a.Status
FROM APPOINTMENT a
JOIN PATIENT p ON a.Patient_ID = p.Patient_ID
JOIN DOCTOR d ON a.Doctor_ID = d.Doctor_ID
JOIN DEPARTMENT dept ON d.Department_ID = dept.Department_ID;

-- Use the view like a table:
-- SELECT * FROM VW_APPOINTMENT_SUMMARY;

-- ---------------------------------------------------------------------
-- 6. DCL - USER MANAGEMENT (Admin & Normal users)
-- ---------------------------------------------------------------------
CREATE USER IF NOT EXISTS 'hms_admin'@'%' IDENTIFIED BY 'AdminPass@123';
GRANT ALL PRIVILEGES ON HospitalManagementDB.* TO 'hms_admin'@'%';

CREATE USER IF NOT EXISTS 'hms_staff'@'%' IDENTIFIED BY 'StaffPass@123';
GRANT SELECT, INSERT, UPDATE ON HospitalManagementDB.* TO 'hms_staff'@'%';
REVOKE DELETE ON HospitalManagementDB.* FROM 'hms_staff'@'%';

FLUSH PRIVILEGES;
-- Verify with: SHOW GRANTS FOR 'hms_admin'@'%';

-- ---------------------------------------------------------------------
-- 7. CASCADED DELETE / UPDATE
-- ---------------------------------------------------------------------
-- Check actual FK constraint names first (MySQL auto-names them):
-- SELECT TABLE_NAME, CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
-- WHERE CONSTRAINT_TYPE='FOREIGN KEY' AND TABLE_SCHEMA='HospitalManagementDB';
-- If names differ from below, swap them in.

ALTER TABLE APPOINTMENT DROP FOREIGN KEY appointment_ibfk_1;
ALTER TABLE APPOINTMENT ADD CONSTRAINT fk_appt_patient
    FOREIGN KEY (Patient_ID) REFERENCES PATIENT(Patient_ID)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE MEDICAL_RECORD DROP FOREIGN KEY medical_record_ibfk_3;
ALTER TABLE MEDICAL_RECORD ADD CONSTRAINT fk_mr_appt
    FOREIGN KEY (Appointment_ID) REFERENCES APPOINTMENT(Appointment_ID)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE PRESCRIPTION DROP FOREIGN KEY prescription_ibfk_1;
ALTER TABLE PRESCRIPTION ADD CONSTRAINT fk_pres_record
    FOREIGN KEY (Record_ID) REFERENCES MEDICAL_RECORD(Record_ID)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE PRESCRIPTION_MEDICINE DROP FOREIGN KEY prescription_medicine_ibfk_1;
ALTER TABLE PRESCRIPTION_MEDICINE ADD CONSTRAINT fk_pm_prescription
    FOREIGN KEY (Prescription_ID) REFERENCES PRESCRIPTION(Prescription_ID)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE BILL DROP FOREIGN KEY bill_ibfk_2;
ALTER TABLE BILL ADD CONSTRAINT fk_bill_appt
    FOREIGN KEY (Appointment_ID) REFERENCES APPOINTMENT(Appointment_ID)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- ---------------------------------------------------------------------
-- 8. TRIGGER - Auto-decrement medicine stock on new prescription
-- ---------------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER trg_update_stock
AFTER INSERT ON PRESCRIPTION_MEDICINE
FOR EACH ROW
BEGIN
    UPDATE MEDICINE
    SET Stock_Quantity = Stock_Quantity - NEW.Quantity
    WHERE Medicine_ID = NEW.Medicine_ID;
END$$
DELIMITER ;

-- ---------------------------------------------------------------------
-- 9. PROCEDURE - Book an appointment and auto-generate its bill
-- ---------------------------------------------------------------------
DELIMITER $$
CREATE PROCEDURE sp_BookAppointmentWithBill(
    IN p_patient_id INT,
    IN p_doctor_id INT,
    IN p_date DATE,
    IN p_time TIME,
    IN p_reason VARCHAR(255),
    IN p_consultation_charge DECIMAL(10,2)
)
BEGIN
    DECLARE new_appt_id INT;
    DECLARE new_bill_id INT;

    SELECT IFNULL(MAX(Appointment_ID), 300) + 1 INTO new_appt_id FROM APPOINTMENT;
    SELECT IFNULL(MAX(Bill_ID), 700) + 1 INTO new_bill_id FROM BILL;

    INSERT INTO APPOINTMENT (Appointment_ID, Patient_ID, Doctor_ID, Appointment_Date, Appointment_Time, Status, Reason)
    VALUES (new_appt_id, p_patient_id, p_doctor_id, p_date, p_time, 'Scheduled', p_reason);

    INSERT INTO BILL (Bill_ID, Patient_ID, Appointment_ID, Bill_Date, Consultation_Charge, Medicine_Charge, Other_Charges, Total_Amount, Payment_Status)
    VALUES (new_bill_id, p_patient_id, new_appt_id, p_date, p_consultation_charge, 0.00, 0.00, p_consultation_charge, 'Unpaid');

    SELECT new_appt_id AS Appointment_ID, new_bill_id AS Bill_ID;
END$$
DELIMITER ;

-- Test it:
-- CALL sp_BookAppointmentWithBill(204, 101, '2026-09-25', '14:00:00', 'Follow-up', 500.00);

-- =====================================================================
-- END OF SCRIPT
-- =====================================================================
