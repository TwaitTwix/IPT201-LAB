# Dataset Integration Guide

## Current Dataset (Tertiary-Level)

The system is pre-configured with a **synthetic tertiary-level dataset** containing **300 university students** designed specifically for university-level academic performance prediction.

### Dataset Statistics

- **Total Students**: 300
- **Student ID Range**: STU1000 - STU1299
- **Attendance Range**: 60-100%
- **Study Hours Range**: 5-25 hours per week
- **Assignment Scores Range**: 50-100%
- **Quiz Scores Range**: 50-100%
- **Predicted Grades**: Calculated using weighted formula
- **Final Grades**: Actual grades with realistic variance

### Data Distribution

The dataset includes students across the full performance spectrum:
- **High Performers** (90-100%): ~30% of students
- **Above Average** (80-89%): ~30% of students
- **Average** (70-79%): ~25% of students
- **Below Average** (60-69%): ~15% of students

### Prediction Algorithm

The AI prediction model uses the following formula:

```
Predicted Grade = (Attendance × 0.25) + (Study Hours × 2.5) + (Assignments × 0.25) + (Quiz Score × 0.25)
```

**Weights:**
- Attendance: 25%
- Study Hours: Scaled to 2.5 (max 25 hours → 62.5 points)
- Assignments: 25%
- Quiz Score: 25%

### Default Login Accounts

#### Admin Account
- Username: `admin`
- Password: `password123`
- Role: Administrator
- Permissions: View all students, teachers, system reports

#### Teacher Account
- Username: `teacher`
- Password: `password123`
- Role: Teacher
- Permissions: Encode grades, track attendance, generate reports

#### Sample Student Accounts
- Username: `stu1000` through `stu1299`
- Password: `student123`
- Role: Student
- Permissions: View own profile, use predictor, view reports

## Optional: Open University Dataset

If you want to use the larger **Open University Learning Analytics Dataset** (32,593 students):

### Download Instructions

1. Go to: https://www.kaggle.com/datasets/rocki37/open-university-learning-analytics-dataset
2. Click "Download"
3. Extract the ZIP file
4. You'll find these key files:
   - `studentInfo.csv` - Student demographic information
   - `studentAssessment.csv` - Grade and score data
   - `studentVle.csv` - Virtual learning environment interactions
   - `assessments.csv` - Assessment information

### Integration Steps

1. Place `studentInfo.csv` and `studentAssessment.csv` in the `data/` folder
2. Create a PHP import script (or use the Python script provided)
3. Map the Open University fields to the application schema:
   - Open University `score` → `final_grade`
   - Open University interactions → `study_hours` (estimated)
   - Calculate `predicted_grade` using the formula above

### Schema Mapping

| Application Field | Open University Field | Notes |
|---|---|---|
| student_id | id_student | Student identifier |
| program | code_module | Course/module name |
| final_grade | score | Assessment score (0-100) |
| attendance | (calculated) | Estimated from VLE interactions |
| study_hours | (calculated) | Estimated from interaction frequency |
| quiz_score | score | Use as-is or calculate from assessment type |
| assignments | score | Use as-is or derive from data |

### Citation

If using the Open University dataset, please cite:

**Kuzilek J., Hlosta M., Zdrahal Z. (2017).** Open University Learning Analytics dataset. Scientific Data, **4**, 170171. https://doi.org/10.1038/sdata.2017.171

## Dataset File Location

Current dataset: `/data/student_performance.csv`

The CSV file should contain these columns:
```
student_id, student_name, attendance, study_hours, assignments, quiz_score, predicted_grade, final_grade
```

## Regenerating the Dataset

To regenerate the synthetic dataset:

```bash
cd scripts
python generate_dataset.py
```

This will create a new 500-student dataset with random realistic values.

## Notes

- The current synthetic dataset is sufficient for development and testing
- For production use with real institution data, consider the Open University dataset or your own data
- All student data is anonymized
- The prediction model can be adjusted by modifying the weights in `/includes/functions.php`
