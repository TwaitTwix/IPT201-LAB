import pandas as pd
import urllib.request
import zipfile
import os

workspace = r'c:\Users\Leo\Documents\INTERSESSION 2ndYR going 3rdYR\Integrative Programming\FINAL PROJECT GROUP 1'
data_dir = os.path.join(workspace, 'data')
os.makedirs(data_dir, exist_ok=True)

# URL to the Open University Learning Analytics dataset from Kaggle
# This uses the direct download link
url = 'https://www.kaggle.com/api/v1/datasets/download/rocki37/open-university-learning-analytics-dataset'

print("Note: To use this dataset, you need to:")
print("1. Sign up at Kaggle.com")
print("2. Download the dataset manually from:")
print("   https://www.kaggle.com/datasets/rocki37/open-university-learning-analytics-dataset")
print("3. Place the extracted CSV files in the 'data' folder")
print()
print("Alternatively, we can generate a synthetic tertiary-level dataset with 500+ students...")
print()

# Since direct download from Kaggle requires authentication, let's generate a synthetic dataset
# that mimics university-level student performance data
import random
random.seed(42)

students = []
student_id = 1000

# Generate 500 university students with realistic academic metrics
for i in range(500):
    # Realistic university-level metrics
    attendance = random.randint(60, 100)
    study_hours = random.randint(5, 25)
    assignment_score = random.randint(50, 100)
    quiz_score = random.randint(50, 100)
    
    # Performance prediction based on weighted formula
    predicted = round((attendance * 0.25) + (study_hours * 2.5) + (assignment_score * 0.25) + (quiz_score * 0.25))
    predicted = min(100, max(0, predicted))
    
    # Final grade (close to predicted for realistic correlation)
    final_grade = min(100, max(0, predicted + random.randint(-5, 5)))
    
    students.append({
        'student_id': f'STU{student_id}',
        'student_name': f'Student_{i+1}',
        'attendance': attendance,
        'study_hours': study_hours,
        'assignments': assignment_score,
        'quiz_score': quiz_score,
        'predicted_grade': predicted,
        'final_grade': final_grade
    })
    student_id += 1

# Create DataFrame and save to CSV
df = pd.DataFrame(students)
output_path = os.path.join(data_dir, 'student_performance.csv')
df.to_csv(output_path, index=False)

print(f"✓ Generated synthetic tertiary-level dataset with {len(students)} students")
print(f"✓ Saved to: {output_path}")
print()
print("Dataset statistics:")
print(f"  Attendance range: {df['attendance'].min()}-{df['attendance'].max()}%")
print(f"  Study hours range: {df['study_hours'].min()}-{df['study_hours'].max()} hours")
print(f"  Assignment scores range: {df['assignments'].min()}-{df['assignments'].max()}%")
print(f"  Quiz scores range: {df['quiz_score'].min()}-{df['quiz_score'].max()}%")
print(f"  Final grades range: {df['final_grade'].min()}-{df['final_grade'].max()}%")
print()
print("To use the official Open University dataset instead:")
print("1. Download from Kaggle manually")
print("2. Extract studentInfo.csv and studentAssessment.csv")
print("3. Place them in the 'data' folder")
print("4. Run the alternative import script to transform them")
