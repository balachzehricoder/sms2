import csv
import random
from faker import Faker
from datetime import datetime

fake = Faker()
Faker.seed(0)
random.seed(0)

# Updated class and section IDs
class_ids = list(range(5, 18))  # class_id from 5 to 17
section_ids = [4, 5, 6, 7]
genders = ["Male", "Female"]
religions = ["Islam", "Christianity", "Hinduism", "Sikhism"]
statuses = ["Active", "Inactive"]

with open('students_demo_data.csv', 'w', newline='', encoding='utf-8') as file:
    writer = csv.writer(file)
    writer.writerow([
        'student_id', 'family_code', 'student_name', 'session', 'class_id', 'section_id', 'gr_no',
        'gender', 'religion', 'dob', 'date_of_admission', 'status', 'whatsapp_number',
        'father_cell_no', 'mother_cell_no', 'home_cell_no', 'place_of_birth', 'state', 'city',
        'email', 'father_name', 'mother_name', 'home_address', 'created_at', 'student_image',
        'admission_fee', 'monthly_fee', 'cnic'
    ])

    for i in range(1, 1001):
        student_name = fake.name()
        family_code = f"FC{random.randint(1000, 9999)}"
        session = f"{random.randint(2018, 2025)}-{random.randint(2019, 2026)}"
        class_id = random.choice(class_ids)
        section_id = random.choice(section_ids)
        gr_no = f"GR{1000 + i}"
        gender = random.choice(genders)
        religion = random.choice(religions)
        dob = fake.date_of_birth(minimum_age=5, maximum_age=18).strftime('%Y-%m-%d')
        date_of_admission = fake.date_between(start_date='-5y', end_date='today').strftime('%Y-%m-%d')
        status = random.choice(statuses)
        whatsapp_number = fake.phone_number()
        father_cell_no = fake.phone_number()
        mother_cell_no = fake.phone_number()
        home_cell_no = fake.phone_number()
        place_of_birth = fake.city()
        state = fake.state()
        city = fake.city()
        email = fake.email()
        father_name = fake.name_male()
        mother_name = fake.name_female()
        home_address = fake.address().replace('\n', ', ')
        created_at = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        student_image = f"default_student_{i%10}.jpg"
        admission_fee = random.randint(1000, 5000)
        monthly_fee = random.randint(500, 2000)
        cnic = f"{random.randint(10000, 99999)}-{random.randint(1000000, 9999999)}-{random.randint(1, 9)}"

        writer.writerow([
            i, family_code, student_name, session, class_id, section_id, gr_no, gender, religion,
            dob, date_of_admission, status, whatsapp_number, father_cell_no, mother_cell_no,
            home_cell_no, place_of_birth, state, city, email, father_name, mother_name,
            home_address, created_at, student_image, admission_fee, monthly_fee, cnic
        ])
