<?php
/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE.txt file within this package)
 * @version   2.0.4
 */

namespace setasign\FpdiPdfParser\PdfParser;

use setasign\FpdiPdfParser\PdfParser\CrossReference\CrossReference;

/**
 * A PDF parser class
 *
 * @package setasign\FpdiPdfParser\PdfParser
 */
class PdfParser extends \setasign\Fpdi\PdfParser\PdfParser
{
    /**
     * Get the cross reference instance.
     *
     * @return CrossReference
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     */
    public function getCrossReference()
    {
        if ($this->xref === null) {
            $this->xref = new CrossReference($this, $this->resolveFileHeader());
        }

        return $this->xref;
    }
}
