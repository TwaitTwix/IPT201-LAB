# ml_prediction_csv.py
import numpy as np
import pandas as pd
from sklearn.linear_model import LinearRegression
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_squared_error, r2_score
import matplotlib.pyplot as plt

def load_and_prepare_data(csv_filename='SMLPA.csv'):
    """
    Loads data from CSV file and prepares it for modeling
    """
    try:
        # Load the CSV file
        df = pd.read_csv(csv_filename)
        
        # Display basic info about the data
        print("=" * 60)
        print("DATA LOADED FROM CSV FILE")
        print("=" * 60)
        print(f"File: {csv_filename}")
        print(f"Number of records: {len(df)}")
        print(f"Columns: {list(df.columns)}")
        print()
        
        # Check if required columns exist
        if 'Hours_Studied' not in df.columns or 'Exam_Scores' not in df.columns:
            print("Error: CSV must contain 'Hours_Studied' and 'Exam_Scores' columns!")
            print(f"Available columns: {list(df.columns)}")
            return None
        
        # Display first few rows of data:
        print("First 5 rows of data:")
        print(df.head())
        print()
        
        print("Dataset Statistics:")
        print(df.describe())
        print()
        
        return df
        
    except Exception as e:
        print(f"Error loading CSV file: {e}")
        return None

def train_and_predict(df):
    """
    Trains a Linear Regression model and makes predictions
    """
    # Prepare features (X) and target (y)
    X = df[['Hours_Studied']]  # Features (2D array)
    y = df['Exam_Scores']       # Target (1D array)
    
    # Split data into training and testing sets (80% train, 20% test)
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42
    )
    
    # Create and train the model
    model = LinearRegression()
    model.fit(X_train, y_train)
    
    # Get model parameters
    slope = model.coef_[0]
    intercept = model.intercept_
    
    print("=" * 60)
    print("LINEAR REGRESSION MODEL - TRAINING COMPLETE")
    print("=" * 60)
    print(f"Equation: Exam Score = {slope:.2f} × Hours Studied + {intercept:.2f}")
    print()
    
    # Make predictions on test data
    y_pred = model.predict(X_test)
    
    # Calculate model performance metrics
    mse = mean_squared_error(y_test, y_pred)
    r2 = r2_score(y_test, y_pred)
    
    print("Model Performance Metrics:")
    print(f"R² Score: {r2:.4f}")
    print(f"Mean Squared Error: {mse:.4f}")
    print()
    
    return model, X, y

def make_prediction(model):
    """
    Gets user input for hours studied and makes prediction
    """
    print("=" * 60)
    print("MAKE A PREDICTION")
    print("=" * 60)
    
    # Ask user for hours studied
    try:
        hours = float(input("Enter number of hours studied: "))
    except ValueError:
        print("Invalid input! Using default value of 6 hours.")
        hours = 6
    
    # Make prediction
    predicted_score = model.predict([[hours]])
    
    print()
    print("=" * 60)
    print("PREDICTION RESULT")
    print("=" * 60)
    print(f"Student studied for: {hours} hours")
    print(f"Predicted Exam Score: {predicted_score[0]:.2f}")
    print("=" * 60)
    print()
    
    return hours, predicted_score[0]

def visualize_results(model, df, predict_hours, predict_score):
    """
    Creates a visualization of the data and the regression line
    """
    plt.figure(figsize=(12, 7))
    
    # Plot the original data points
    plt.scatter(df['Hours_Studied'], df['Exam_Scores'], 
               color='blue', s=150, label='Training Data', zorder=5, alpha=0.7)
    
    # Plot the regression line
    x_range = np.linspace(df['Hours_Studied'].min() - 0.5, 
                         df['Hours_Studied'].max() + 1.5, 100).reshape(-1, 1)
    y_range = model.predict(x_range)
    plt.plot(x_range, y_range, color='red', linewidth=3, 
            label='Regression Line', zorder=3)
    
    # Plot the prediction point
    plt.scatter(predict_hours, predict_score, 
               color='green', s=300, marker='*', 
               label=f'Prediction: {predict_score:.2f}', zorder=10, 
               edgecolors='darkgreen', linewidth=2)
    
    # Add labels and title
    plt.xlabel('Hours Studied', fontsize=14, fontweight='bold')
    plt.ylabel('Exam Score', fontsize=14, fontweight='bold')
    plt.title('Linear Regression: Hours Studied vs Exam Score', 
             fontsize=16, fontweight='bold')
    plt.grid(True, alpha=0.3, linestyle='--')
    plt.legend(loc='lower right', fontsize=12)
    
    # Set axis limits with some padding
    x_min = max(0, df['Hours_Studied'].min() - 1)
    x_max = df['Hours_Studied'].max() + 1.5
    y_min = max(0, df['Exam_Scores'].min() - 10)
    y_max = df['Exam_Scores'].max() + 10
    
    plt.xlim(x_min, x_max)
    plt.ylim(y_min, y_max)
    
    # Annotate the prediction point
    plt.annotate(f'({predict_hours}, {predict_score:.2f})', 
                xy=(predict_hours, predict_score),
                xytext=(predict_hours + 0.3, predict_score + 3),
                fontsize=11, fontweight='bold',
                bbox=dict(boxstyle='round,pad=0.3', facecolor='yellow', alpha=0.3))
    
    # Add value labels on data points
    for i, row in df.iterrows():
        plt.annotate(f'({row["Hours_Studied"]}, {row["Exam_Scores"]})', 
                    xy=(row["Hours_Studied"], row["Exam_Scores"]),
                    xytext=(3, 3), textcoords='offset points',
                    fontsize=9, alpha=0.7)
    
    plt.tight_layout()
    plt.savefig('ml_prediction_plot.png', dpi=300, bbox_inches='tight')
    plt.show()
    print("✅ Visualization saved as 'ml_prediction_plot.png'")

def main():
    """
    Main function to run the complete ML pipeline
    """
    print("\n" + "=" * 60)
    print("SIMPLE MACHINE LEARNING PREDICTION APPLICATION")
    print("=" * 60 + "\n")
    
    # Load data from CSV
    df = load_and_prepare_data('SMLPA.csv')
    if df is None:
        return
    
    # Train the model
    model, X, y = train_and_predict(df)
    
    # Make prediction
    hours, predicted_score = make_prediction(model)
    
    # Visualize results
    visualize_results(model, df, hours, predicted_score)
    
    print("\n" + "=" * 60)
    print("PROGRAM COMPLETED SUCCESSFULLY!")
    print("=" * 60)

if __name__ == "__main__":
    main()