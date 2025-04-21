import bcrypt

# Get password input from user
password = input("Enter your password: ")

# Convert to bytes
password_bytes = password.encode('utf-8')

# Generate a salt (default cost factor = 12)
salt = bcrypt.gensalt()

# Generate the bcrypt hash
hashed_password = bcrypt.hashpw(password_bytes, salt)

# Print the result
print("Hashed password:")
print(hashed_password.decode('utf-8'))
