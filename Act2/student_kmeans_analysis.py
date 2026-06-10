import argparse
import os
import pandas as pd
import numpy as np
from sklearn.preprocessing import StandardScaler
from sklearn.cluster import KMeans
from sklearn.decomposition import PCA
import matplotlib.pyplot as plt
import seaborn as sns

sns.set(style='whitegrid')

FEATURES = [
    'Hours_Studied_Per_Week',
    'Attendance_Percent',
    'Assignments_Completed',
    'Quiz_Average',
    'Project_Score',
    'Internet_Usage_Hours_Per_Day',
    'Extracurricular_Activities_Per_Month'
]


def load_data(path):
    if not os.path.exists(path):
        raise FileNotFoundError(f"File not found: {path}")
    df = pd.read_csv(path)
    return df


def explore(df):
    print('\nFirst 5 records:')
    print(df.head().to_string(index=False))
    print('\nShape:', df.shape)
    print('\nMissing values per column:')
    print(df.isna().sum())
    print('\nSummary statistics:')
    print(df.describe().T)


def preprocess(df, features):
    data = df[features].copy()
    # handle missing values by median
    data = data.fillna(data.median())
    scaler = StandardScaler()
    X_scaled = scaler.fit_transform(data)
    return X_scaled, scaler


def elbow_method(X, k_max=10, out_path='elbow.png'):
    wcss = []
    for k in range(1, k_max+1):
        km = KMeans(n_clusters=k, random_state=42, n_init=10)
        km.fit(X)
        wcss.append(km.inertia_)
    plt.figure(figsize=(8,5))
    plt.plot(range(1, k_max+1), wcss, marker='o')
    plt.xlabel('Number of clusters K')
    plt.ylabel('WCSS (Inertia)')
    plt.title('Elbow Method for optimal K')
    plt.xticks(range(1, k_max+1))
    plt.tight_layout()
    plt.savefig(out_path)
    plt.close()
    print(f"Elbow plot saved to {out_path}")
    print('WCSS values:')
    for idx, val in enumerate(wcss, start=1):
        print(f'K={idx}: {val:.2f}')
    # attempt automated elbow detection via second derivative
    if len(wcss) >= 3:
        second_diff = np.diff(np.diff(wcss))
        elbow_k = int(np.argmin(second_diff) + 2)
    else:
        elbow_k = 1
    print(f'Auto-detected elbow K = {elbow_k}')
    return elbow_k


def fit_kmeans(X, k):
    km = KMeans(n_clusters=k, random_state=42, n_init=20)
    labels = km.fit_predict(X)
    return km, labels


def save_clustered(df, labels, out_csv='STUDENT-KMEANS-clustered.csv'):
    out = df.copy()
    out['Cluster'] = labels
    out.to_csv(out_csv, index=False)
    print(f'Clustered dataset saved to {out_csv}')
    return out


def cluster_summary(df_with_clusters, features, out_csv='cluster_summary.csv'):
    summary = df_with_clusters.groupby('Cluster')[features].mean().round(3)
    summary.to_csv(out_csv)
    print(f'Cluster summary saved to {out_csv}')
    return summary


def plot_pca(X, labels, out_path='clusters_pca.png'):
    pca = PCA(n_components=2, random_state=42)
    components = pca.fit_transform(X)
    dfp = pd.DataFrame({
        'PC1': components[:,0],
        'PC2': components[:,1],
        'Cluster': labels
    })
    plt.figure(figsize=(8,6))
    sns.scatterplot(data=dfp, x='PC1', y='PC2', hue='Cluster', palette='tab10', s=60)
    plt.title('K-Means clusters (PCA projection)')
    plt.tight_layout()
    plt.savefig(out_path)
    plt.close()
    print(f'PCA cluster plot saved to {out_path}')


def recommendations(out_path='recommendations.txt'):
    recs = [
        '1) Academic tutoring programs targeted to clusters flagged as At Risk: provide weekly peer or instructor-led tutoring focusing on weaker subjects.',
        '2) Time-management and study-skills workshops for Average performers to help them improve consistency and assignment completion.',
        '3) Scholarship and advanced opportunities for High-performing students to encourage retention and excellence.',
        '4) Attendance monitoring with early alerts for students whose attendance drops below a threshold combined with counseling.',
        '5) Offer digital literacy and healthy internet-usage guidance for students with very high internet hours to reduce distraction.'
    ]
    with open(out_path, 'w') as f:
        f.write('\n'.join(recs))
    print(f'Recommendations written to {out_path}')
    return recs


def main():
    parser = argparse.ArgumentParser(description='Student K-Means Clustering Analysis')
    parser.add_argument('--input', '-i', default='STUDENT-KMEANS.csv', help='Input CSV file')
    parser.add_argument('--k', '-k', type=int, default=None, help='Number of clusters to use (optional)')
    args = parser.parse_args()

    df = load_data(args.input)
    explore(df)

    X_scaled, scaler = preprocess(df, FEATURES)

    elbow_k = elbow_method(X_scaled, k_max=10, out_path='elbow.png')

    k_chosen = args.k if (args.k is not None) else elbow_k if elbow_k >= 1 else 3
    print(f'Using K = {k_chosen} for K-Means')

    km, labels = fit_kmeans(X_scaled, k_chosen)
    df_clustered = save_clustered(df, labels, out_csv='STUDENT-KMEANS-clustered.csv')

    summary = cluster_summary(df_clustered, FEATURES, out_csv='cluster_summary.csv')
    print('\nCluster means:')
    print(summary)

    plot_pca(X_scaled, labels, out_path='clusters_pca.png')

    recs = recommendations(out_path='recommendations.txt')

    print('\nAnalysis complete. Generated files:')
    print('- STUDENT-KMEANS-clustered.csv')
    print('- cluster_summary.csv')
    print('- elbow.png')
    print('- clusters_pca.png')
    print('- recommendations.txt')

if __name__ == '__main__':
    main()
