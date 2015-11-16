Project rewritten from https://github.com/christinemcclure/leisure-process


# To generate a new file

set environment variable: OCLC_DEV_KEY for OCLC WorldSearch API

1. Run in directory where web server (with PHP interpreter) has write access (not \var\www)
2. Obtain a list of ISBNs and matching CRNS from bookstore 'course ID' report. 
3. Delete the first several lines of the file until you get to the data
4. Re-order the columns so that ISBN is first, followed by CRN. 
5. Save the file as CSV. 
6. From command line, run 'PHP process-textbooks.php'

# TODO
- Strip extra characters and ' " * from line to clean data
