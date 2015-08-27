Code base from https://github.com/christinemcclure/leisure-process
NOTE: This could be modified to only use XML rather than parsing into JSON first. 


# To generate a new file

set environment variable: OCLC_DEV_KEY

1. Install Node.js with NPM on a workstation
2. Clone this repository to the workstation.
3. Run npm install to create the package
4. Obtain a list of ISBNs and matching CRNS from bookstore 'course ID' report. Save the file as textbooks-input.csv (create input options in the future) in the directory. NOTE: file format: CSV, with ISBN in the first column; CRN in second
5. Run node index.js to generate the data file: textbooks-output-info.csv

