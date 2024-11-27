-- Insert a customer (user_id = 1)
INSERT INTO users (full_name, email, phone_number, password, role) 
VALUES ('Ara', 'Ara@gmail.com', '1234567890', 'ara123123', 'customer');

-- Insert some therapists (user_id = 2, 3)
INSERT INTO users (full_name, email, phone_number, password, role) 
VALUES ('Sebby', 'Sebby@gmail.com', '0987654321', 'sebby123321', 'therapist'),
       ('Nikka', 'Nikka@gmail.com', '1122334455', 'nikka123456', 'therapist');


