<img src="../assets/fontawesome/svgs-full/solid/.svg" alt="" width="16" height="16" style="filter:invert(1)">

# Weighing scales PHP serial (Recommended Scale Hardware)

1. CAS LP-Series - USB/Serial connection, PHP compatible
2. Mettler Toledo - Professional scales with SDK
3. Dibal Scales - Good PHP integration options
4. Adam Equipment - Affordable with serial output
5. Custom Bluetooth Scales - Using ESP32/Arduino

ALTER TABLE credit_balances
ADD COLUMN balance double GENERATED ALWAYS AS (CONCAT(first_name, ' ', last_name)) STORED;


Git Bash

; cd /c/laragon/www/butchery
# Add a .gitignore (Optional but Recommended): If this is a Laravel project, you don't want to upload your .env file or vendor folder. If you don't have a .gitignore file yet, create a simple one:

; touch .gitignore

# Stage and Commit:

; git add .
; git commit -m "Initial commit of butchery project"

# Connect to GitHub
# Go to your GitHub account (github.com/sittilyani) and create a new repository named butchery. Do not initialize it with a README or License there (since we already have code locally).
# Once created, run these commands in Git Bash:
# Link the remote:

; git remote add origin https://github.com/sittilyani/butchery.git

# Rename the branch to 'main':

Bash
; git branch -M main

# Push your code:

Bash
; git push -u origin main

# If the project was cloned and it bringing issues then remove the link like this

# Run these commands in order inside your Git Bash:

# Remove the old link
# First, let's break the connection to the pharmacy repo:

Bash
; git remote remove origin

# Verify it’s gone
Run this to make sure no URLs show up:

Bash
; git remote -v

# (If it returns nothing, you're in the clear.)

# Link to the new Butchery repo
# Now, tell Git where the code should actually go. Make sure you have already created the repository named butchery on your GitHub account first.

Bash

; git remote add origin https://github.com/sittilyani/butchery.git

# Push the code
# Now try to push again. We use -u (upstream) this time to "set" the new memory:

Bash

; git push -u origin main


