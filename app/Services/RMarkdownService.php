<?php
namespace App\Services;
use Response;

class RMarkdownService 
{

    /**
     * Constructor
     * @param void
     * @return void
     */
    public function __construct() 
    {
        $this->documentRoot = $_SERVER['DOCUMENT_ROOT'];
    }

        

    /**
     * Generate Head of RMarkdown File
     * @param $title, 
     * @return void
     */
    public function generateTitle($title) 
    {
        $fp = fopen("$this->documentRoot/bar.Rmd", 'wb');
        $HeadOfRmdFile =
        "---\n"
        ."title: \"{$title}\"\n"
        ."output:\n"
        ."flexdashboard::flex_dashboard:\n"
        ."    orientation: rows\n"
        ."    social: menu\n"
        ."---\n"
        ."```{r setup, include=FALSE}\n"
        ."library(flexdashboard)\n"
        ."library(dygraphs)\n"
        ."library(xts)\n";
        fwrite($fp, $HeadOfRmdFile, strlen($HeadOfRmdFile));
        fclose($fp);
    }

    /**
     * Write R scripts for each appearance occasion
     * eg. "event", "work", "student", "invite", etc.. 
     * 
     * @param $firstYear
     * @param $lastYear
     * @param $firstMonth
     * @param $lastMonth
     * @param $appearances
     * @param $key
     * 
     * @return void
     * 
     */
    public function generateMemberAppearancesRmd(
        $firstYear, 
        $lastYear, 
        $firstMonth, 
        $lastMonth, 
        $appearances, 
        $key,
        $limit
    ) 

    {
        // Open RMarkdown file to append
        $fp = fopen("$this->documentRoot/bar.Rmd", 'ab');

        static $count; // to keep track of function calls
        static $KEYS = [];

        if ($count !== $limit) 
        {
            // insert data into R script for each occasion
            $appendString = 
            "$key.ByMonthYear <- c(".$this->extractDataFromArray($appearances)."\n"
            ."$key.TS <- ts( $key.ByMonthYear, start = c({$firstYear},{$firstMonth}), end = c({$lastYear},{$lastMonth}), frequency = 12)\n"
            ."$key.TS_AS_XTS <- as.xts($key.TS)\n\n";

            $count++;
            array_push($KEYS, $key);

            // append R script to file 
            fwrite($fp, $appendString, strlen($appendString));
        } 
        else if ($count === $limit) // on last call
        {
            $all = 
            "```\n"
            ."Row {.tabset .tabset-fade}\n"
            ."-------------------------------------\n";
            "### All appearances\n"
            ."```{r}\n"
            ."dygraph(allTS_AS_XTS) %>%\n"
            ."dyOptions(drawPoints = TRUE, pointSize = 2) %>%\n"
            ."dyRangeSelector()\n"
            ."```\n";

            $occasions = "";

            foreach ($KEYS as $KEY ) {
                $occasions .=                 
                "### {$KEY}s!\n"
                ."```{r}\n"
                ."dygraph({$KEY}TS_AS_XTS) %>%\n"
                ."dyOptions(drawPoints = TRUE, pointSize = 2) %>%\n"
                ."dyRangeSelector()\n"
                ."```\n";
            }

        $appendString = $all.$occasions;
        fwrite($fp, $appendString, strlen($appendString));

        }
        fclose($fp);
    }    

    /**
     * Generate member signup data graph
     */
    public function generateMemberJoinsRmd(
        $firstYear, 
        $lastYear, 
        $firstMonth, 
        $lastMonth, 
        $data
    ) 
    {
        $fp = fopen("$this->documentRoot/foo.Rmd", 'wb');

        // R markdown formatted string
        $outputString = 
            "---\n"
            ."title: \"Member sign ups from {$firstYear} to {$lastYear}\""
            ."\noutput:\n" 
            ."  flexdashboard::flex_dashboard:\n"
            ."    orientation: rows\n"
            ."    social: menu\n"
            ."---\n\n"
            ."```{r setup, include=FALSE}\n"
            ."library(flexdashboard)\n"
            ."library(dygraphs)\n"
            ."library(xts)\n"
            ."joinsByMonthYear <-" 
            ." c(".$this->extractDataFromArray($data).")\n"
            ."joinTS <- ts( joinsByMonthYear, start= c({$firstYear},{$firstMonth}), end= c({$lastYear},{$lastMonth}), frequency = 12)\n"
            ."joinTS_AS_XTS <- as.xts(joinTS)\n"
            ."```\n"
            ."Row {.tabset .tabset-fade}\n"
            ."-------------------------------------\n"
            ."### All incubators\n"
            ."```{r}\n"
            ."dygraph(joinTS_AS_XTS) %>%\n" 
            ."dyOptions(drawPoints = TRUE, pointSize = 2) %>%\n"
            ."dyRangeSelector()\n"  
            ."```\n";
        fwrite($fp, $outputString, strlen($outputString) );
        fclose($fp);
    }

   // extract and format values from array to print in an R array c(1,2,3,4....)
   private function extractDataFromArray($data) 
   {
        $extractedData = "";
        foreach ($data as $datum) 
        {
            // format from [1,2,3...n] to 1,2,3,...n
            $extractedData = ltrim($extractedData.','.$datum, ',');
        }
        return $extractedData;
    }
}