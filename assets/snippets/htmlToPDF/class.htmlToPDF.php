<?php
/**
 * Handles PDF header and footer options, extends the TCPDF class-
 *
 * @name htmlToPDF
 *
 * @author Stefanie Janine Stoelting, mail@stefanie-stoelting.de
 * @link http://code.google.com/p/htmlToPDF/
 * @link http://www.stefanie-stoelting.de/phptohtml-news.html
 * @link http://www.tcpdf.org/
 * @package htmlToPDF
 * @license LGPL
 * @since 2011/02/14
 * @version 0.1.alpha1
 */
class htmlToPDF extends TCPDF {
  /**
   * Constant string The default header font type
   */
  const DEFAULT_FONT_TYPE = 'helvetica';

  /**
   * Constant int The default header font size
   */
  const DEFAULT_HEADER_FONT_SIZE = 16;

  /**
   * Constant int The default footer font size
   */
  const DEFAULT_FOOTER_FONT_SIZE = 8;

  /**
   * Constant int The default position of the footer from the bottom
   */
  const DEFAULT_FOOTER_POSITION_FROM_BOTTOM = 15;

  /**
   * Constant string The path to the TCPDF image folder
   */
  const TCPFD_IMAGE_FOLDER = '/assets/lib/tcpdf/images/';

  /**
   * The path and name of the image file.
   * @var string
   */
  private $_imageFile = '';

  /**
   * The file type of the image, only jpg, gif, or png are allowed.
   * @var string
   */
  private $_imageFileType = '';

  /**
   * The header text for the PDF file.
   * @var string
   */
  private $_headerText = '';

  /**
   * The font type for the header, default is helvetica.
   * @var string
   */
  private $_headerFontType = htmlToPDF::DEFAULT_FONT_TYPE;

  /**
   * The font size for the header text, default is 20.
   * @var int
   */
  private $_headerFontSize = htmlToPDF::DEFAULT_HEADER_FONT_SIZE;

  /**
   * Whether the header text is bold, or not, default is true.
   * @var string
   */
  private $_headerFontBold = 'B';

  /**
   * The font type for the footer, default is helvetica.
   * @var string
   */
  private $_footerFontType = htmlToPDF::DEFAULT_FONT_TYPE;

  /**
   * The font size for the footer, default is 8.
   * @var int
   */
  private $_footerFontSize = htmlToPDF::DEFAULT_FOOTER_FONT_SIZE;

  /**
   * Whether the footer text is italic, or not, default is true.
   * @var string
   */
  private $_footerFontItalic = 'I';

  /**
   * The caption text for 'page', default is english page.
   * @var string
   */
  private $_footerPageCaption = 'Page';

  /**
   * The page separator for page ../.., default is /.
   * @var string
   */
  private $_footerPageSeparator = '/';

  /**
   * The footer postion from the bottom of the page in mm, default is 15 mm.
   * @var int
   */
  private $_footerPositionFromBottom = htmlToPDF::DEFAULT_FOOTER_POSITION_FROM_BOTTOM;

  /**
   * The content footer text.
   * @var string
   */
  private $_contentFooter = '';

  /**
   * Contains the keywords for the document.
   * @var string
   */
  private $_keyWords = '';

  /**
   * Contains the CSS styles for the PDF documents
   * @var string
   */
  private $_cssStyle = '';


  /**
   * Replaces placeholders in the given text and returns content.
   *
   * @global object $modx
   * @param string $content The content where the placeholders will be replaced
   * @param string $dateFormat The format string for date formats, default is Y-m-d
   * @return string The content with the replaced placeholders
   */
  private function replacePlaceholder($content, $dateFormat='Y-m-d')
  {
    global $modx;

    $result = $content;

    if (!empty($content)) {
      // Replace [+pagetitle+]
      $result = str_replace(
              '[+pagetitle+]',
              $modx->documentObject['pagetitle'],
              $result
              );

      // Replace [+longtitle+]
      $result = str_replace(
              '[+longtitle+]',
              $modx->documentObject['longtitle'],
              $result
              );

      // Replace [+website+]
      $result = str_replace(
              '[+website+]',
              $modx->getConfig('site_url'),
              $result
              );

      // Replace [+currentsite+]
      $result = str_replace(
              '[+currentsite+]',
              $modx->makeUrl((int)$modx->documentObject['id'], '', '', 'full'),
              $result
              );

      // Replace [+author+]
      $user = $modx->getUserInfo($modx->documentObject['editedby']);
      $result = str_replace(
              '[+author+]',
              $user[fullname],
              $result
              );

      // Replace [+date+]
      $result = str_replace(
              '[+date+]',
              date($dateFormat, $modx->documentObject['publishedon']),
              $result
              );
    }

    return $result;
  } // replacePlaceholder

  /**
   * Sets the image file for the header.
   *
   * @param string $value The image file name with its full path, allowed file extensions are jpg, gif, or png
   * @throws If the file does not exists
   * @throws If the image file has no extension
   * @throws If the file extesnions is not one of e the allowed file extensions are jpg, gif, or png
   */
  public function setImageFile($value)
  {
    global $modx;

    $checkFile = MODX_BASE_PATH . self::TCPFD_IMAGE_FOLDER . $value;

    if (file_exists($checkFile)) {
      $tmp = explode('.', $value);

      if (count($tmp) > 1) {
        $fileType = strtoupper($tmp[count($tmp)-1]);

        if(in_array($fileType, array('JPG', 'GIF', 'PNG'))) {
          // MODX_BASE_PATH
          $this->_imageFile = $value;
          $this->_imageFileType = $fileType;
        } else {
          throw new Exception('The file extension is not jpg, gif, or png');
        }
      } else {
        throw new Exception('The image file name has no extension.');
      }
    } else {
      throw new Exception('The given file name does not exist.');
    }
  } // setImageFile

  /**
   * Returns the image file including the path to the image.
   *
   * @return string The path to the image file
   */
  public function getImageFile()
  {
    return $this->_imageFile;
  } // getImageFile

  /**
   * Sets the header text of the PDF.
   *
   * @global object $modx
   * @param string $chunk The name of the chunk, that contains the header
   * @param string $dateFormat The format string for date formats, default is Y-m-d
   * @return string The content with the replaced placeholders
   */
  public function setHeaderText($chunk, $dateFormat='Y-m-d')
  {
    global $modx;

    $this->_headerText = '';

    if (!empty($chunk)) {
      $this->_headerText = $this->replacePlaceholder($modx->getChunk($chunk), $dateFormat);
    }

    return $this->_headerText;
  } // setHeaderText

  /**
   * Returns the header text.
   * 
   * @return string The header text
   */
  public function getHeaderText()
  {
    return $this->_headerText;
  } // getHeaderText

  /**
   * Sets the header font type.
   *
   * @param string $value The name of the font for the header
   * @throws If the given value is empty
   */
  public function setHeaderFontType($value)
  {
    if(!empty($value)) {
      $this->_headerFontType = $value;
    } else {
      throw new Exception('The font type can\'t be empty.');
    }
  } // setHeaderFontType

  /**
   * Returns the header font type.
   *
   * @return string The header font type
   */
  public function getHeaderFontType()
  {
    return $this->_headerFontType;
  } // getHeaderFontType

  /**
   * Sets the bold option for the header text.
   *
   * @param boolean $value If the header font is bold, or not
   * @throws If the given value is not a boolean
   */
  public function setHeaderFontBold($value)
  {
    if (is_bool($value)) {
      if ($value) {
        $this->_headerFontBold = 'B';
      } else {
        $this->_headerFontBold = '';
      }
    } else {
      throw new Exception('The value is not a boolean');
    }
  } // setHeaderFontBold

  /**
   * Returns the bold shortcut.
   *
   * @return string The bold shortcut if true, otherwise empty string
   */
  public function getHeaderFontBold()
  {
    return $this->_headerFontBold;
  } // getHeaderFontBold

  /**
   * Sets the font size of the header text.
   *
   * @param float $value The font size of the header text
   * @throws If the given value is not a number
   */
  public function setHeaderFontSize($value)
  {
    if(is_numeric($value)) {
      $this->_headerFontSize = $value;
    } else {
      throw new Exception('The font size is not numeric.');
    }
  } // setHeaderFontSize

  /**
   *  Returns the font size of the header.
   *
   * @return int The font size of the header
   */
  public function getHeaderFontSize()
  {
    return $this->_headerFontSize;
  } // getHeaderFontSize

  /**
   * Sets the footer font type.
   *
   * @param string $value The name of the font for the footer
   * @throws If the given value is empty
   */
  public function setFooterFontType($value)
  {
    if (!empty($value)) {
      $this->_footerFontType = $this->_footerFontType;
    } else {
      throw new Exception('The font type can\'t be empty.');
    }
  } // setFooterFontType

  /**
   * Returns the footer font type
   * 
   * @return string The footer font type
   */
  public function getFooterFontType()
  {
    return $this->_footerFontType;
  } // getFooterFontType

  /**
   * Sets the italic option for the footer text.
   *
   * @param boolean $value If the footer font is italic, or not
   * @throws If the given value is not a boolean
   */
  public function setFooterFontItalic($value)
  {
    if (is_bool($value)) {
      if ($value) {
        $this->_footerFontItalic = 'I';
      } else {
        $this->_footerFontItalic = '';
      }
    } else {
      throw new Exception('The value is not a boolean');
    }
  } // setFooterFontBold

  /**
   * Returns the footer font italic.
   *
   * @return string Empty, if false, otherwise I for italic
   */
  public function getFooterFontItalic()
  {
    return $this->_footerFontItalic;
  } // getFooterFontItalic

  /**
   * Sets the font size of the footer text.
   *
   * @param float $value The font size of the footer text
   * @throws If the given value is not numeric
   */
  public function setFooterFontSize($value)
  {
    if(is_numeric($value)) {
      $this->_footerFontSize = $value;
    } else {
      throw new Exception('The font size is not numeric.');
    }
  } // setFooterFontSize

  /**
   * Returns the footer font size.
   *
   * @return int The footer font size
   */
  public function getFooterFontSize()
  {
    return $this->_footerFontSize;
  } // getFooterFontSize

  /**
   * Sets the page text.
   *
   * @param string $value The footer caption text (translation) for page
   */
  public function setFooterPageCaption($value)
  {
    $this->_footerPageCaption = $value;
  } // setFooterPageCaption

  /**
   * Sets the page number separator.
   *
   * @param string $value Sets the separator between current page and of pages
   */
  public function setFooterPageSeparator($value)
  {
    $this->_footerPageSeparator = $value;
  } // setFooterPageSeparator

  /**
   * Sets the position of the footer from the bottom of a page.
   *
   * @param int $value The position of the footer from the bottom of a page in mm
   * @throws If value is not an integer
   */
  public function setFooterPositionFromBottom($value)
  {
    if (is_int($value)) {
      // The position has to be a negative number
      if ($value < 0) {
        $multiplier = 1;
      } else {
        $multiplier = -1;
      }

      $this->_footerPositionFromBottom = $value * ($multiplier);
    } else {
      throw new Exception('The position from bottom has to be an integer.');
    }
  } // setFooterPositionFromBottom

  /**
   * Sets chunk defined content under the document content. The placeholders
   * in the chunk are replaced with the appropriate content.
   *
   * @global object $modx
   * @param <type> $chunk The name of the content chunk
   * @param <type> $dateFormat The date format, default is Y-m-d
   * @return string The
   */
  public function setContentFooter($chunk, $dateFormat='Y-m-d')
  {
    global $modx;

    $this->_contentFooter = '';
    
    if (!empty($chunk)) {
      $this->_contentFooter = $this->replacePlaceholder($modx->getChunk($chunk), $dateFormat);
    }

    return $this->_contentFooter;
  } // setContentFooter

  /**
   * Returns the content footer with the replaced placeholsers.
   *
   * @return string The content footer HTML
   */
  public function getContentFooter()
  {
    return $this->_contentFooter;
  } // getContentFooter

  /**
   * Reads the keyword from a template variable.
   * 
   * @global object $modx
   * @param $tvName $value The name of the template variable
   */
  public function SetKeywords($tvName)
  {
    global $modx;
    $modxHelper = modxHelper::getInstance();

    if (!empty($value)) {
      $this->_keyWords = $modxHelper->getTVContent($value, $modx->documentObject['id']);
    } else {
      $this->_keyWords = '';
    }
    parent::SetKeywords($this->_keyWords);
  } // setKeywords

  public function getKeywords()
  {
    return $this->_keyWords;
  } // getKeywords

  /**
   * Overrides the orignial SetHeaderData. This is the way for using an easy
   * site header.
   *
   * @global object $modx
   */
  public function SetHeaderData()
  {
    global $modx;

    list($width, $height, $type, $attr) = getimagesize(
            MODX_BASE_PATH . self::TCPFD_IMAGE_FOLDER . $this->getImageFile());

    //die('$width: ' .$width . ' image name: ' . $this->getImageFile());
    parent::SetHeaderData(
            $this->getImageFile(),
            //$width,
            20,
            $modx->documentObject['pagetitle'],
            $this->getHeaderText()
            );
  } // SetHeaderData

  /**
   * The style is checked for beginning and end tags (<style> and </style>). If
   * the beginning or the end tag are not set, they are set here.
   *
   * @global object $modx
   * @param string $chunk The chunk containing the CSS style
   * @return string The CSS style for the PDF document
   */
  public function setCSS($chunk)
  {
    global $modx;

    $this->_cssStyle = $modx->getChunk($chunk);

    // Check whether the style start exists
    if (!strpos($css, '<style>')) {
      $this->_cssStyle = '<style>' . $this->_cssStyle;
    }

    // Check whether the style end exists
    if (!strpos($css, '</style>')) {
      $this->_cssStyle = $this->_cssStyle . '</style>';
    }

    // Add a line break
    $this->_cssStyle .= "\n";

    return $this->_cssStyle;
  } // setCSS

  /**
   * Returns the CSS styles for the PDF document.
   *
   * @return string The CSS style for the PDF document
   */
  public function getCSS()
  {
    return $this->_cssStyle;
  } // getCSS

  /**
   * Returns whether to use CSS style, or not.
   *
   * @return boolean Wether to use CSS style, or not
   */
  public function useCSS()
  {
    return !empty($this->_cssStyle);
  } // useCSS
} // htmlToPDF