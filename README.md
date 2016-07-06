Project rewritten from https://github.com/christinemcclure/leisure-process


# To generate a new file

set environment variable: OCLC_DEV_KEY for OCLC WorldSearch API

1. Run in directory where web server (with PHP interpreter) has write access (not \var\www). It's easiest to just run it locally until this becomes a full-fledged module.
2. For textbooks, obtain a list of ISBNs and matching CRNS from bookstore 'course ID' report. 

    a. Re-order the columns so that ISBN is first, followed by CRN. 

    b. Add a blank third column, and add  semester that the books are for. Valid entries: spring, fall, summer. The script will ensure they are in lowercase.

    c. There is no need to delete the other columns. An improvement to the script would be to add a header row and have the program determine which columns are isbn, crn, and semester.

    d. Save the file as CSV. 

3. for leisure books, only need ISBNs.
4. Run from command line



