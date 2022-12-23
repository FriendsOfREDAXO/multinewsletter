<?php
/**
  * CSV class
  * Basic CSV import/export, Excel-compatible by default
  * Usage: Set delimiter and quote character according to
  * current needs, i.e. it's possible (and often sensible)
  * to change delimiter and quote character between import
  * and export using the interface (setDelimiter, setQuote).
  * @author Jens Hatlak <jh@junetz.de>,
  *         Simon Bichler <bichlesi@in.tum.de>,
  *         tajhlande <tajhlande@gmail.com>
  * @version 1.3 12/30/2007
  * @package Junetz
  */
  class CSV {
  /**
    * Field delimiter. Default: ';'
    * For tab separation set this to '\t'
    * @var string
    */
    var $delimiter = ";";
  /**
    * Quote character. Default: '"'
    * @var string
    */
    var $quote = '"';
  /**
    * No. of preview lines. Default: 5
    * @var int
    */
    var $prelines = 5;
  /**
    * No. of columns. Default: 0 (no limit)
    * @var int
    */
    var $cols = 0;
  /**
    * Display/return/export header line
    * @var boolean
    */
    var $head = true;
  /**
    * Line end character. Default: "\n"
    * @var string
    */
    var $lineend = "\n";
  /**
    * Escape character. Default: '"'
    * @var string
    */
    var $escape = '"';
  /**
    * 2D data array of lines (second dimension: fields).
    * @var array
    */
    var $data;

  /**
    * Constructor
    * @param string $del Delimiter
    * @quote string $quote Quote character
    */
    function CSV($del=";", $quote='"', $lineend="\n", $escape='"') {
      $this->setDelimiter($del);
      $this->setQuote($quote);
      $this->setLineEnd($lineend);
      $this->setEscape($escape);
      $this->data = [];
    }

  /**
    * Set Delimiter
    * @param string $del New delimiter
    */
    function setDelimiter($del) {
      if (!empty($del)) $this->delimiter = $del;
    }

  /**
    * Set Quote character
    * @param mixed $q New quote character (string or false)
    */
    function setQuote($q) {
      if (empty($q))
        $q = "";
      else if (is_string($q))
        $this->quote = $q;
    }

  /**
    * Set no. of columns (for next import)
    * @param int $cols New no. of columns
    */
    function setCols($cols) {
      if (is_numeric($cols)) $this->cols = $cols;
    }

  /**
    * Set no. of preview lines
    * @param int $lines New no. of lines
    */
    function setPreLines($lines) {
      if (is_numeric($lines)) $this->prelines = $lines;
    }

  /**
    * Set heading switch.
    * Affects preview, reSort, exStream
    * @param boolean $head Activate heading switch
    */
    function setHead($head) {
      $this->head = (boolean) $head;
    }

  /**
    * Set line end character
    * @param string $lineend Line end character
    */
    function setLineEnd($lineend) {
      if (is_string($lineend)) $this->lineend = $lineend;
    }

  /**
    * Set escape character
    * @param string $escape Escape character
    */
    function setEscape($escape) {
      if (is_string($escape)) $this->escape = $escape;
    }

  /**
    * Get current delimiter
    * @return string Delimiter
    */
    function getDelimiter() {
      return $this->delimiter;
    }

  /**
    * Get current quote character
    * @return string Quote character
    */
    function getQuote() {
      return $this->quote;
    }

  /**
    * Get current column limit
    * @return int No. of cols
    */
    function getCols() {
      return $this->cols;
    }

  /**
    * Get current no. of preview lines
    * @return int No. of preview lines
    */
    function getPreLines() {
      return $this->prelines;
    }

  /**
    * Get current heading switch state
    * @return boolean Heading switch value
    */
    function getHead() {
      return $this->head;
    }

  /**
    * Get current line end character
    * @return string Line end character
    */
    function getLineEnd() {
      return $this->lineend;
    }

  /**
    * Get current escape character
    * @return string Escape character
    */
    function getEscape() {
      return $this->escape;
    }

  /**
    * Get data
    * @return array 2D array of lines containing field arrays
    */
    function getData() {
      if ($this->head) return array_slice($this->data, 1);
      return $this->data;
    }

  /**
    * Add a line containing fields as array or complete string
    * to the internal data (line) array
    * @param mixed Line contents
    */
    function addLine($data) {
      if (is_array($data)) $this->data[] = $data;
      else if (is_string($data)) {
        $this->data[] = $this->parseLine($data);
      }
    }

  /**
    * Parse a line string according to current settings
    * (delimiter/quote)
    * @return array Parsed data
    */
    function parseLine($str) {
      $data = $this->splitWithEscape($str);
      foreach ($data as $key=>$val)
        $data[$key] = str_replace($this->quote, '', $val);
      return $data;
    }

  /**
    * Returns the smaller value of
    * no. of preview lines and actual no. of lines.
    * Neither of the values is being altered
    * @return int Smaller value
    */
    function checkLineCount() {
      $reallines = count($this->data);
      if ($this->prelines>$reallines)
        return $reallines;
      return $this->prelines;
    }

  /**
    * Formats a line according to current settings
    * (delimiter/quote)
    * @param array $data Array of fields (strings)
    * @return string Formatted line
    */
    function formatLine($data) {
      foreach ($data as $str)
        $line .= sprintf('%s%s%s%s', $this->quote, $str,
                         $this->quote, $this->delimiter);
      return substr($line,0,-1).$this->lineend;
    }

  /**
    * Splits a string into an array of tokens, delimited by delimiter char.
    * Tokens in input string containing the delimiter character or the
    * literal escape character are surrounded by a pair of escape characters.
    * A literal escape character is produced by the escape character
    * appearing twice in sequence
    * @author tajhlande <tajhlande@gmail.com>
    */
  function splitWithEscape($str) {
    $len = strlen($str);
    $tokens = [];
    $i = 0;
    $inEscapeSeq = false;
    $currToken = '';
    while ($i < $len) {
      $c = substr($str, $i, 1);
      if ($inEscapeSeq) {
        if ($c == $this->escape) {
          // lookahead to see if next character is also an escape char
          if ($i == ($len - 1)) {
            // c is last char, so must be end of escape sequence
            $inEscapeSeq = false;
          } else if (substr($str, $i + 1, 1) == $this->escape) {
            // append literal escape char
            $currToken .= $this->escape;
            $i++;
          } else {
            // end of escape sequence
            $inEscapeSeq = false;
          }
        } else {
          $currToken .= $c;
        }
      } else {
        if ($c == $this->delimiter) {
          // end of token, flush it
          array_push($tokens, $currToken);
          $currToken = '';
        } else if ($c == $this->escape) {
          // begin escape sequence
          $inEscapeSeq = true;
        } else {
          $currToken .= $c;
        }
      }
      $i++;
    }
    // flush the last token
    array_push($tokens, $currToken);
    return $tokens;
  }

  /**
    * Re-sort internal data array using field $field
    * @param int $field Field index
    * @param mixed $dir Direction (const. SORT_ASC or SORT_DESC)
    */
    function reSort($field, $dir=SORT_ASC) {
      if (is_numeric($field) && ($dir==SORT_ASC || $dir==SORT_DESC)) {
        foreach ($this->data as $key=>$val) {
          if ($this->head && $key==0) {
            // make sure heading is the first line
            if ($dir==SORT_ASC) $sortarray[] = -2147483647;
            else if ($dir==SORT_DESC) $sortarray[] = "zzz";
          } else
            $sortarray[] = $val[$field];
        }
        array_multisort($this->data, SORT_STRING, $sortarray, $dir);
      }
    }

  /**
    * Re-sort internal data array using multiple fields
    * [$field, $dir, $type]...
    * @param int $field Field index
    * @param mixed $dir Direction (const. SORT_ASC or SORT_DESC)
    * @param int $type Type of comparison
    *                  0 = Case sensitive natural
    *                  1 = Case insensitive natural
    *                  2 = Numeric
    *                  3 = Case sensitive string
    *                  4 = Case insensitive string
    * Example: $csv->multiSort(0, SORT_ASC, 2, 1, SORT_DESC, 1);
    *          would result in the data array being sorted in numeric
    *          ascending order by the first field. If several rows
    *          have the same value in the first field, these rows
    *          would be sorted in Case Insensitive descending order
    *          by their second fields.
    * @author Simon Bichler <bichlesi@in.tum.de>
    */
    function multiSort() {
      $code = '';

      for ($i = 0; $i < func_num_args(); $i += 3) {
        $field = func_get_arg($i);

        $dir = SORT_ASC;
        if ($i + 1 < func_num_args())
          $dir = func_get_arg($i + 1);

        $type = 0;
        if ($i + 2 < func_num_args())
          $type = func_get_arg($i + 2);

        $code .= 'if ($a['.$field.'] != $b['.$field.']) {';

        switch ($type) {
          case 1: // Case insensitive natural.
            if ($dir == SORT_ASC) {
              $code .= 'return (strcasenatcmp($a['.$field.'],$b['.$field.']));}';
            } else {
              $code .= 'return (-strcasenatcmp($a['.$field.'],$b['.$field.']));}';
            }
            break;
          case 2: // Numeric.
            if ($dir == SORT_ASC) {
              $code .= 'return (($a['.$field.'] == $b['.$field.']) ? 0:(($a['.$field.'] < $b['.$field.']) ? -1 : 1));}';
            } else {
              $code .= 'return (-(($a['.$field.'] == $b['.$field.']) ? 0:(($a['.$field.'] < $b['.$field.']) ? -1 : 1)));}';
            }
            break;
          case 3: // Case sensitive string.
            if ($dir == SORT_ASC) {
              $code .= 'return (strcmp($a['.$field.'],$b['.$field.']));}';
            } else {
              $code .= 'return (-strcmp($a['.$field.'],$b['.$field.']));}';
            }
            break;
          case 4: // Case insensitive string.
            if ($dir == SORT_ASC) {
              $code .= 'return (strcasecmp($a['.$field.'],$b['.$field.']));}';
            } else {
              $code .= 'return (-strcasecmp($a['.$field.'],$b['.$field.']));}';
            }
            break;
          default: // Case sensitive natural.
            if ($dir == SORT_ASC) {
              $code .= 'return (strnatcmp($a['.$field.'],$b['.$field.']));}';
            } else {
              $code .= 'return (-strnatcmp($a['.$field.'],$b['.$field.']));}';
            }
            break;
        }
      }

      $code .= 'return 0;';

      if ($this->head) {
        $head = $this->data[0];
        unset($this->data[0]);
        uasort($this->data, create_function('$a,$b', $code));
        $this->data = array_merge(array(0=>$head),$this->data);
      } else {
        uasort($this->data, create_function('$a,$b', $code));
      }
    }

  /**
    * Select rows from internal data array by removing every row that
    * does not match at least one of the given comparisons.
    * [$field, $operator, $compare, $type] ...
    * This simulates an SQL statement like
    * SELECT * FROM $this->data WHERE $field $operator $compare OR $field $operator $compare OR ...
    * To simulate a SELECT ... AND ... AND ... statement you can call this function repeatedly.
    * @param int $field Field index
    * @param string $operator Operator for comparison
    *               "="  : equals
    *               "<"  : less than
    *               "<=" : less or equal
    *               ">"  : greater than
    *               ">=" : greater or equal
    * @param mixed $compare The value to compare with $this->data[$field]
    * @param int $type Type of comparison
    *                  0 = Case sensitive natural
    *                  1 = Case insensitive natural
    *                  2 = Numeric
    *                  3 = Case sensitive string
    *                  4 = Case insensitive string
    * @author Simon Bichler <bichlesi@in.tum.de>
    */
    function select() {
      $code = '';

      for ($i = 0; $i < func_num_args(); $i += 4) {
        $field = func_get_arg($i);
        $operator = func_get_arg($i + 1);
        $compare = func_get_arg($i + 2);
        $type = func_get_arg($i + 3);

        if ($operator == "=") {
          $code .= 'if ($a['.$field.'] == "'.$compare.'") {return true;}';
        } else {
          switch ($type) {
            case 1: // Case insensitive natural.
              if ($operator == "<") {
                $code .= 'if (strcasenatcmp($a['.$field.'],"'.$compare.'") < 0) {return true;}';
              } else if ($operator == "<=") {
                $code .= 'if (strcasenatcmp($a['.$field.'],"'.$compare.'") <= 0) {return true;}';
              } else if ($operator == ">") {
                $code .= 'if (strcasenatcmp($a['.$field.'],"'.$compare.'") > 0) {return true;}';
              } else if ($operator == ">=") {
                $code .= 'if (strcasenatcmp($a['.$field.'],"'.$compare.'") >= 0) {return true;}';
              }
              break;
            case 2: // Numeric.
              if ($operator == "<") {
                $code .= 'if ($a['.$field.'] < '.$compare.') {return true;}';
              } else if ($operator == "<=") {
                $code .= 'if ($a['.$field.'] <= '.$compare.') {return true;}';
              } else if ($operator == ">") {
                $code .= 'if ($a['.$field.'] > '.$compare.') {return true;}';
              } else if ($operator == ">=") {
                $code .= 'if ($a['.$field.'] >= '.$compare.') {return true;}';
              }
              break;
            case 3: // Case sensitive string.
              if ($operator == "<") {
                $code .= 'if (strcmp($a['.$field.'],"'.$compare.'") < 0) {return true;}';
              } else if ($operator == "<=") {
                $code .= 'if (strcmp($a['.$field.'],"'.$compare.'") <= 0) {return true;}';
              } else if ($operator == ">") {
                $code .= 'if (strcmp($a['.$field.'],"'.$compare.'") > 0) {return true;}';
              } else if ($operator == ">=") {
                $code .= 'if (strcmp($a['.$field.'],"'.$compare.'") >= 0) {return true;}';
              }
              break;
            case 4: // Case insensitive string.
              if ($operator == "<") {
                $code .= 'if (strcasecmp($a['.$field.'],"'.$compare.'") < 0) {return true;}';
              } else if ($operator == "<=") {
                $code .= 'if (strcasecmp($a['.$field.'],"'.$compare.'") <= 0) {return true;}';
              } else if ($operator == ">") {
                $code .= 'if (strcasecmp($a['.$field.'],"'.$compare.'") > 0) {return true;}';
              } else if ($operator == ">=") {
                $code .= 'if (strcasecmp($a['.$field.'],"'.$compare.'") >= 0) {return true;}';
              }
              break;
            default: // Case sensitive natural.
              if ($operator == "<") {
                $code .= 'if (strnatcmp($a['.$field.'],"'.$compare.'") < 0) {return true;}';
              } else if ($operator == "<=") {
                $code .= 'if (strnatcmp($a['.$field.'],"'.$compare.'") <= 0) {return true;}';
              } else if ($operator == ">") {
                $code .= 'if (strnatcmp($a['.$field.'],"'.$compare.'") > 0) {return true;}';
              } else if ($operator == ">=") {
                $code .= 'if (strnatcmp($a['.$field.'],"'.$compare.'") >= 0) {return true;}';
              }
              break;
          }
        }
      }

      $code .= 'return false;';

      $compare_function = create_function('$a', $code);

      foreach ($this->data as $key=>$val) {
        if (!($this->head && $key == 0)) {
          if ($compare_function($val) == false) {
            unset($this->data[$key]);
          }
        }
      }
    }

  /**
    * Import data from arbitrary MySQL query
    * @param resource $res MySQL result resource
    */
    function queryImport($res) {
      $fc = mysql_num_fields($res);
      if ($fc==0) return;
      for ($i=0; $i < $fc; $i++)
        $data[] = mysql_field_name($res,$i);
      $this->addLine($data);
      while ($row = mysql_fetch_row($res)) {
        $data = [];
        for ($i=0; $i < $fc; $i++)
          $data[] = $row[$i];
        $this->addLine($data);
      }
    }

  /**
    * Export data from arbitrary MySQL query
    * @param resource $res MySQL result resource
    * @param string $name Preset file name
    * @param string $ext Extension (default: "csv")
    * @param boolean $nameContainsExt Wether $name contains $ext (default: false)
    */
    function queryExport($res, $name, $ext, $nameContainsExt=false) {
      $fc = mysql_num_fields($res);
      if ($fc==0) return;
      $this->sendHeaders($name, $ext, $nameContainsExt);
      if ($this->head) {
        for ($i=0; $i < $fc; $i++)
          $data[] = mysql_field_name($res,$i);
        echo $this->formatLine($data);
      }
      while ($row = mysql_fetch_row($res))
        echo $this->formatLine($line);
      exit;
    }

  /**
    * Returns preview data
    * (up to no. of preview lines)
    * @return array 2D Array of lines containing fields
    */
    function preview() {
      if ($this->head) $start = 0;
      else $start = 1;
      for ($i=$start;$i<$this->checkLineCount();$i++)
        $data[] = $this->data[$i];
      return $data;
    }

  /**
    * Import uploaded file
    * @param string $field Name of fileselect field
    * @param int $length Optional maximal line length (default: 1024)
    */
    function uplImport($field, $length=1024) {
      if (!$GLOBALS["HTTP_POST_FILES"][$field]["error"])
        $this->fimport($GLOBALS["HTTP_POST_FILES"][$field]["tmp_name"], $length);
    }

  /**
    * Import file
    * @param string $file Name of file to be imported
    * @param int $length Optional maximal line length (default: 1024)
    */
    function fImport($file="", $length=1024) {
      if ($file!="" && file_exists($file)) {
		ini_set("auto_detect_line_endings", true);
        $fp = fopen($file,"r");
        while ($data = fgetcsv($fp, $length, $this->delimiter)) {
          if ($this->cols!=0)
            $data = array_slice($data, 0, $this->cols);
          $this->data[] = $data;
        }
      }
    }

  /**
    * Open export file stream (HTTP download)
    * @param string $name Preset file name
    * @param string $ext Extension (default: "csv")
    * @param boolean $nameContainsExt Wether $name contains $ext (default: false)
    */
    function exStream($name, $ext="csv", $nameContainsExt=false) {
      if (empty($this->data)) return;
      $this->sendHeaders($name, $ext, $nameContainsExt);

      foreach ($this->data as $nr=>$line) {
        if ($this->head || $nr!=0)
          $datastr .= $this->formatLine($line);
      }

      // Dateigröße für Downloadzeit-Berechnung
      header("Content-Length: ".strlen($datastr));

      // Adressen ausgeben und Script beenden
      echo $datastr;
      exit;
    }

  /**
    * Send appropriate headers
    * @param string $name File name (may already contain extension,
    * in which case the second parameter is ignored)
    * @param string $ext Extension (default: "csv")
    */
    function sendHeaders($name, $ext="csv") {
      global $HTTP_USER_AGENT;
      header("Content-Type: text/text");
      if (!preg_match('/\.\w+$/', $name)) $name = "$name.$ext";
      header("Content-Disposition: ".
        (preg_match("/MSIE 5.5/", $HTTP_USER_AGENT)?"":"attachment; ").
        "filename=$name");
      header("Content-Description: PHP Generated Data");
      header("Content-Transfer-Encoding: binary");
      header("Cache-Control: post-check=0, pre-check=0");
      header("Connection: close");
    }
  }