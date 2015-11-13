Project rewritten from https://github.com/christinemcclure/leisure-process


# To generate a new file

set environment variable: OCLC_DEV_KEY for OCLC WorldSearch API

1. Run in directory where web server (with PHP interpreter) has write access (not \var\www)
4. Obtain a list of ISBNs and matching CRNS from bookstore 'course ID' report. NOTE: file format: CSV, with ISBN in the first column; CRN(s) in second (multiple courses use the same textbook)
5. From command line, run PHP process-textbooks.php

# TODO
- Strip extra characters and ' " * from line to clean data
