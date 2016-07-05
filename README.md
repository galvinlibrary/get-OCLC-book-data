Project rewritten from https://github.com/christinemcclure/leisure-process


# To generate a new file

set environment variable: OCLC_DEV_KEY for OCLC WorldSearch API

1. Run in directory where web server (with PHP interpreter) has write access (not \var\www). It's easiest to just run it locally until this becomes a full module.
2. For textbooks, obtain a list of ISBNs and matching CRNS from bookstore 'course ID' report. 

    a. Re-order the columns so that ISBN is first, followed by CRN

    b. Add a third column for semester that the books are for. Valid entries: spring, fall, summer. The script will ensure they are in lowercase.

    c. Save the file as CSV. 

3. for leisure books, only need ISBNs.
4. Run from command line

