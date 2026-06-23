# api_integration.py
import requests
import json

def fetch_and_display_users():
    """
    Fetches user data from JSONPlaceholder API and displays formatted output
    """
    try:
        # API endpoint
        url = "https://jsonplaceholder.typicode.com/users"
        
        # Make GET request
        response = requests.get(url)
        
        # Check if request was successful
        response.raise_for_status()
        
        # Parse JSON response
        users = response.json()
        
        # Display header
        print("=" * 60)
        print("USER INFORMATION FROM JSONPLACEHOLDER API")
        print("=" * 60)
        print()
        
        # Display each user's information
        for idx, user in enumerate(users, 1):
            print(f"User #{idx}")
            print("-" * 40)
            print(f"Name:        {user['name']}")
            print(f"Email:       {user['email']}")
            print(f"Company:     {user['company']['name']}")
            print()
        
        print("=" * 60)
        print(f"Total users displayed: {len(users)}")
        print("=" * 60)
        
        return users
        
    except requests.exceptions.RequestException as e:
        print(f"Error connecting to API: {e}")
        return None
    except KeyError as e:
        print(f"Error parsing data: Missing key {e}")
        return None
    except json.JSONDecodeError as e:
        print(f"Error decoding JSON: {e}")
        return None

if __name__ == "__main__":
    fetch_and_display_users()