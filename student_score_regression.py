import os
import pandas as pd
from sklearn.linear_model import LinearRegression
from sklearn.model_selection import train_test_split
from sklearn.metrics import r2_score


def load_data(csv_path: str):
    return pd.read_csv(csv_path)


def train_and_evaluate(dataframe):
    features = ["Hours_Studied", "Attendance_Percent", "Assignments_Completed"]
    target = "Exam_Score"

    X = dataframe[features]
    y = dataframe[target]

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42
    )

    model = LinearRegression()
    model.fit(X_train, y_train)
    y_pred = model.predict(X_test)
    score = r2_score(y_test, y_pred)

    return model, score


def predict_from_input(model, feature_names):
    try:
        hours = float(input("Enter hours studied: ").strip())
        attendance = float(input("Enter attendance percentage: ").strip())
        assignments = int(input("Enter assignments completed: ").strip())
    except ValueError:
        print("Invalid input. Please enter numeric values.")
        return

    
    input_data = pd.DataFrame([[hours, attendance, assignments]], 
                              columns=feature_names)
    
    prediction = model.predict(input_data)
    print(f"Predicted Exam Score: {prediction[0]:.2f}")


def main():
    csv_path = os.path.join(os.path.dirname(__file__), "Students.csv")
    data = load_data(csv_path)
    model, r2 = train_and_evaluate(data)

    print(f"Intercept: {model.intercept_:.2f}")
    print(f"Coefficients: {[float(c) for c in model.coef_]}")
    print(f"R² Score: {r2:.2f}")

    feature_names = ["Hours_Studied", "Attendance_Percent", "Assignments_Completed"]

    while True:
        user_choice = input("\nPredict a score from your own inputs? (y/n, q to quit): ").strip().lower()
        if user_choice == "q" or user_choice == "n":
            print("Exiting prediction mode. Goodbye!")
            break
        elif user_choice == "y":
            predict_from_input(model, feature_names)
        else:
            print("Please enter 'y' to predict, 'n' to stop, or 'q' to quit.")


if __name__ == "__main__":
    main()
