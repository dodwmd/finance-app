-- Grant permissions needed for parallel testing
-- This allows the user to create and manage multiple databases required for parallel tests

-- Create the main database if it doesn't exist
-- Note: The database should already be created by MySQL container initialization

-- Grant privileges to the user for all databases (needed for parallel testing)
-- We use finance_user directly instead of an env var since MySQL container handles this already
GRANT ALL PRIVILEGES ON *.* TO 'finance_user'@'%' WITH GRANT OPTION;

-- Allow the user to create databases (needed for parallel testing)
GRANT CREATE ON *.* TO 'finance_user'@'%';

-- Ensure new privileges take effect immediately
FLUSH PRIVILEGES;
