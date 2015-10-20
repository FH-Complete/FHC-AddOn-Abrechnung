<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>

<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="honorare">

<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
  <office:scripts/>
  <office:font-face-decls>
    <style:font-face style:name="DejaVu Sans1" svg:font-family="'DejaVu Sans'" style:font-family-generic="swiss"/>
    <style:font-face style:name="Liberation Serif" svg:font-family="'Liberation Serif'" style:font-family-generic="roman" style:font-pitch="variable"/>
    <style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="Liberation Sans" svg:font-family="'Liberation Sans'" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="DejaVu Sans" svg:font-family="'DejaVu Sans'" style:font-family-generic="system" style:font-pitch="variable"/>
    <style:font-face style:name="Droid Sans Fallback" svg:font-family="'Droid Sans Fallback'" style:font-family-generic="system" style:font-pitch="variable"/>
  </office:font-face-decls>
  <office:automatic-styles>
    <style:style style:name="Tabelle1" style:family="table">
      <style:table-properties style:width="17cm" table:align="margins"/>
    </style:style>
    <style:style style:name="Tabelle1.A" style:family="table-column">
      <style:table-column-properties style:column-width="13.097cm" style:rel-column-width="50487*"/>
    </style:style>
    <style:style style:name="Tabelle1.B" style:family="table-column">
      <style:table-column-properties style:column-width="3.903cm" style:rel-column-width="15048*"/>
    </style:style>
    <style:style style:name="Tabelle1.A1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
    </style:style>
    <style:style style:name="Tabelle2" style:family="table">
      <style:table-properties style:width="17cm" fo:margin-left="-0.037cm" table:align="left"/>
    </style:style>
    <style:style style:name="Tabelle2.A" style:family="table-column">
      <style:table-column-properties style:column-width="1.72cm"/>
    </style:style>
    <style:style style:name="Tabelle2.B" style:family="table-column">
      <style:table-column-properties style:column-width="3.551cm"/>
    </style:style>
    <style:style style:name="Tabelle2.C" style:family="table-column">
      <style:table-column-properties style:column-width="1.102cm"/>
    </style:style>
    <style:style style:name="Tabelle2.D" style:family="table-column">
      <style:table-column-properties style:column-width="1.52cm"/>
    </style:style>
    <style:style style:name="Tabelle2.E" style:family="table-column">
      <style:table-column-properties style:column-width="2.249cm"/>
    </style:style>
    <style:style style:name="Tabelle2.F" style:family="table-column">
      <style:table-column-properties style:column-width="3.616cm"/>
    </style:style>
    <style:style style:name="Tabelle2.G" style:family="table-column">
      <style:table-column-properties style:column-width="1.699cm"/>
    </style:style>
    <style:style style:name="Tabelle2.H" style:family="table-column">
      <style:table-column-properties style:column-width="1.543cm"/>
    </style:style>
    <style:style style:name="Tabelle2.A1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0cm" fo:border="none"/>
    </style:style>
    <style:style style:name="Tabelle4" style:family="table">
      <style:table-properties style:width="17cm" fo:margin-left="-0.037cm" table:align="left"/>
    </style:style>
    <style:style style:name="Tabelle4.A" style:family="table-column">
      <style:table-column-properties style:column-width="2.249cm"/>
    </style:style>
    <style:style style:name="Tabelle4.B" style:family="table-column">
      <style:table-column-properties style:column-width="1.787cm"/>
    </style:style>
    <style:style style:name="Tabelle4.C" style:family="table-column">
      <style:table-column-properties style:column-width="3.814cm"/>
    </style:style>
    <style:style style:name="Tabelle4.D" style:family="table-column">
      <style:table-column-properties style:column-width="2.205cm"/>
    </style:style>
    <style:style style:name="Tabelle4.E" style:family="table-column">
      <style:table-column-properties style:column-width="6.946cm"/>
    </style:style>
    <style:style style:name="Tabelle4.A1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
    </style:style>
    <style:style style:name="Termine" style:family="table">
      <style:table-properties style:width="8.401cm" table:align="left"/>
    </style:style>
    <style:style style:name="Termine.A" style:family="table-column">
      <style:table-column-properties style:column-width="1.300cm"/>
    </style:style>
    <style:style style:name="Termine.B" style:family="table-column">
      <style:table-column-properties style:column-width="1.502cm"/>
    </style:style>
    <style:style style:name="Termine.C" style:family="table-column">
      <style:table-column-properties style:column-width="0.903cm"/>
    </style:style>
    <style:style style:name="Termine.D" style:family="table-column">
      <style:table-column-properties style:column-width="0.993cm"/>
    </style:style>
    <style:style style:name="Termine.E" style:family="table-column">
      <style:table-column-properties style:column-width="0.706cm"/>
    </style:style>
    <style:style style:name="Termine.F" style:family="table-column">
      <style:table-column-properties style:column-width="0.991cm"/>
    </style:style>
    <style:style style:name="Termine.G" style:family="table-column">
      <style:table-column-properties style:column-width="2.21cm"/>
    </style:style>
    <style:style style:name="Termine.A1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #c0c0c0" fo:border-right="none" fo:border-top="0.05pt solid #c0c0c0" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Termine.B1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="0.05pt solid #c0c0c0" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Termine.G1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.05pt solid #c0c0c0" fo:border-top="0.05pt solid #c0c0c0" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Termine.A2" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #c0c0c0" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Termine.B2" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
    </style:style>
    <style:style style:name="Termine.G2" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.05pt solid #c0c0c0" fo:border-top="none" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Termine.ALast" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #c0c0c0" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #c0c0c0"/>
    </style:style>
    <style:style style:name="Termine.BLast" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #c0c0c0"/>
    </style:style>
    <style:style style:name="Termine.GLast" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.05pt solid #c0c0c0" fo:border-top="none" fo:border-bottom="0.05pt solid #c0c0c0"/>
    </style:style>
    <style:style style:name="Termine.A8" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #c0c0c0" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #c0c0c0"/>
    </style:style>
    <style:style style:name="Termine.B8" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #c0c0c0"/>
    </style:style>
    <style:style style:name="Termine.G8" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.05pt solid #c0c0c0" fo:border-top="none" fo:border-bottom="0.05pt solid #c0c0c0"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen" style:family="table">
      <style:table-properties style:width="8.5cm" table:align="margins" style:shadow="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.A" style:family="table-column">
      <style:table-column-properties style:column-width="1.896cm" style:rel-column-width="14619*"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.B" style:family="table-column">
      <style:table-column-properties style:column-width="3.198cm" style:rel-column-width="24655*"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.C" style:family="table-column">
      <style:table-column-properties style:column-width="0.794cm" style:rel-column-width="6119*"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.D" style:family="table-column">
      <style:table-column-properties style:column-width="0.912cm" style:rel-column-width="7030*"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.E" style:family="table-column">
      <style:table-column-properties style:column-width="1.7cm" style:rel-column-width="13112*"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.A1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #c0c0c0" fo:border-right="none" fo:border-top="0.05pt solid #c0c0c0" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.B1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="0.05pt solid #c0c0c0" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.E1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.05pt solid #c0c0c0" fo:border-top="0.05pt solid #c0c0c0" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.A2" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #c0c0c0" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.B2" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.E2" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.05pt solid #c0c0c0" fo:border-top="none" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.ALast" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #c0c0c0" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #c0c0c0"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.BLast" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #c0c0c0"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.ELast" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.05pt solid #c0c0c0" fo:border-top="none" fo:border-bottom="0.05pt solid #c0c0c0"/>
    </style:style>
    <style:style style:name="Sonderhonorare" style:family="table">
      <style:table-properties style:width="8.5cm" table:align="margins"/>
    </style:style>
    <style:style style:name="Sonderhonorare.A" style:family="table-column">
      <style:table-column-properties style:column-width="1.894cm" style:rel-column-width="1074*"/>
    </style:style>
    <style:style style:name="Sonderhonorare.B" style:family="table-column">
      <style:table-column-properties style:column-width="5.009cm" style:rel-column-width="2840*"/>
    </style:style>
    <style:style style:name="Sonderhonorare.C" style:family="table-column">
      <style:table-column-properties style:column-width="1.596cm" style:rel-column-width="905*"/>
    </style:style>
    <style:style style:name="Sonderhonorare.A1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #c0c0c0" fo:border-right="none" fo:border-top="0.05pt solid #c0c0c0" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Sonderhonorare.B1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="0.05pt solid #c0c0c0" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Sonderhonorare.C1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.05pt solid #c0c0c0" fo:border-top="0.05pt solid #c0c0c0" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Sonderhonorare.A2" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #c0c0c0" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Sonderhonorare.B2" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Sonderhonorare.C2" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.05pt solid #c0c0c0" fo:border-top="none" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Sonderhonorare.ALast" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #c0c0c0" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #c0c0c0"/>
    </style:style>
    <style:style style:name="Sonderhonorare.BLast" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #c0c0c0"/>
    </style:style>
    <style:style style:name="Sonderhonorare.CLast" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.05pt solid #c0c0c0" fo:border-top="none" fo:border-bottom="0.05pt solid #c0c0c0"/>
    </style:style>
    <style:style style:name="P1" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties fo:font-size="8pt" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00124ad7" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P2" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P3" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00124ad7" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P4" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00124ad7" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P5" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
    </style:style>
    <style:style style:name="P6" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00124ad7" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
    </style:style>
    <style:style style:name="P7" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" officeooo:rsid="00153d98" officeooo:paragraph-rsid="00153d98" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
    </style:style>
    <style:style style:name="P8" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" officeooo:rsid="00132031" officeooo:paragraph-rsid="0016db00" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
    </style:style>
    <style:style style:name="P9" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" officeooo:rsid="0018d754" officeooo:paragraph-rsid="0018d754" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
    </style:style>
    <style:style style:name="P10" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" fo:font-weight="bold" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00124ad7" style:font-size-asian="7pt" style:font-weight-asian="bold" style:font-size-complex="7pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P11" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" fo:font-weight="bold" officeooo:rsid="00132031" officeooo:paragraph-rsid="0016db00" style:font-size-asian="7pt" style:font-weight-asian="bold" style:font-size-complex="7pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P12" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" fo:font-weight="bold" officeooo:rsid="0018d754" officeooo:paragraph-rsid="0018d754" style:font-size-asian="7pt" style:font-weight-asian="bold" style:font-size-complex="7pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial"/>
    </style:style>
    <style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00124ad7" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="0016db00" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P16" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="00132031" officeooo:paragraph-rsid="0016db00" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00124ad7"/>
    </style:style>
    <style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-weight="bold" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00124ad7" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00124ad7" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P20" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="0016db00" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P21" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="00132031" officeooo:paragraph-rsid="00132031" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P22" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00124ad7" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P23" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="00132031" officeooo:paragraph-rsid="0016db00" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P24" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00124ad7" style:font-size-asian="7pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P25" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" fo:font-weight="bold" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00124ad7" style:font-size-asian="7pt" style:font-weight-asian="bold" style:font-size-complex="7pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P26" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" fo:font-weight="bold" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00132031" style:font-size-asian="7pt" style:font-weight-asian="bold" style:font-size-complex="7pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P27" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" fo:font-weight="bold" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="0016db00" style:font-size-asian="7pt" style:font-weight-asian="bold" style:font-size-complex="7pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P28" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" fo:font-weight="bold" officeooo:rsid="0018d754" officeooo:paragraph-rsid="0018d754" style:font-size-asian="7pt" style:font-weight-asian="bold" style:font-size-complex="7pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P29" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="00132031" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
    </style:style>
    <style:style style:name="P30" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" officeooo:rsid="00124ad7" officeooo:paragraph-rsid="0016db00" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
    </style:style>
    <style:style style:name="P31" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" officeooo:rsid="0016db00" officeooo:paragraph-rsid="0016db00" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
    </style:style>
    <style:style style:name="P32" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" officeooo:rsid="00132031" officeooo:paragraph-rsid="0016db00" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
    </style:style>
    <style:style style:name="PageBreak" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:break-before="page"/>
    </style:style>
    <style:style style:name="T1" style:family="text">
      <style:text-properties style:font-name="Arial"/>
    </style:style>
    <style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
      <style:graphic-properties style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
    </style:style>
    <style:style style:name="Sect1" style:family="section">
      <style:section-properties text:dont-balance-text-columns="true" style:editable="false">
        <style:columns fo:column-count="2" fo:column-gap="0cm">
          <style:column style:rel-width="32767*" fo:start-indent="0cm" fo:end-indent="0cm"/>
          <style:column style:rel-width="32768*" fo:start-indent="0cm" fo:end-indent="0cm"/>
        </style:columns>
      </style:section-properties>
    </style:style>
  </office:automatic-styles>
  <office:body>
	<xsl:apply-templates select="honorar"/>
  </office:body>
</office:document-content>
</xsl:template>

<xsl:template match="honorar">
   <office:text xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
      <text:sequence-decls>
        <text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Table"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Text"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
      </text:sequence-decls>
      <text:p text:style-name="P13">
        <draw:frame draw:style-name="fr1" draw:name="Bild2" text:anchor-type="paragraph" svg:x="0.288cm" svg:y="-0.52cm" svg:width="6.93cm" svg:height="2.108cm" draw:z-index="1">
          <draw:image xlink:href="Pictures/100002010000085000000287322F24E0.png" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
        </draw:frame>
        <draw:frame draw:style-name="fr1" draw:name="Bild1" text:anchor-type="paragraph" svg:x="11.954cm" svg:y="-0.212cm" svg:width="4.974cm" svg:height="1.852cm" draw:z-index="0">
          <draw:image xlink:href="Pictures/10000201000006C900000287007475BF.png" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
        </draw:frame>
      </text:p>
      <text:p text:style-name="P13"/>
      <text:p text:style-name="P13"/>
      <text:p text:style-name="P13"/>
      <text:p text:style-name="P13"/>
      <text:p text:style-name="P13"/>
      <table:table table:name="Tabelle1" table:style-name="Tabelle1">
        <table:table-column table:style-name="Tabelle1.A"/>
        <table:table-column table:style-name="Tabelle1.B"/>
        <table:table-row>
          <table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
            <text:p text:style-name="P14"/>
            <text:p text:style-name="P14"/>
            <text:p text:style-name="P14"/>
            <text:p text:style-name="P14"><xsl:value-of select="anrede" /></text:p>
            <text:p text:style-name="P14"><xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" /><text:line-break/>
				<xsl:value-of select="strasse" /></text:p>
            <text:p text:style-name="P14"><xsl:value-of select="plz" /><xsl:text> </xsl:text><xsl:value-of select="ort" /></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
            <text:p text:style-name="P3">Fachhochschule des bfi Wien</text:p>
            <text:p text:style-name="P3">Gesellschaft m.b.H.</text:p>
            <text:p text:style-name="P3"/>
            <text:p text:style-name="P3">Wohlmutstraße 22</text:p>
            <text:p text:style-name="P3">A-1020 Wien</text:p>
            <text:p text:style-name="P3">Tel: ++43 / 1 / 720 12 86</text:p>
            <text:p text:style-name="P3">Fax: ++43 / 1 / 720 12 86 / 19</text:p>
            <text:p text:style-name="P1">
              <text:span text:style-name="T1">e-mail: </text:span>
              <text:a xlink:type="simple" xlink:href="mailto:info@fh-vie.ac.at" text:style-name="Internet_20_link" text:visited-style-name="Visited_20_Internet_20_Link">
                <text:span text:style-name="T1">info@fh-vie.ac.at</text:span>
              </text:a>
            </text:p>
            <text:p text:style-name="P3">http://www.fh-vie.ac.at</text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P13"/>
      <text:p text:style-name="P17"/>
      <text:p text:style-name="P18">Honorarabrechnung - Übersicht</text:p>
      <text:p text:style-name="P24"/>
      <table:table table:name="Tabelle2" table:style-name="Tabelle2">
        <table:table-column table:style-name="Tabelle2.A"/>
        <table:table-column table:style-name="Tabelle2.B"/>
        <table:table-column table:style-name="Tabelle2.C"/>
        <table:table-column table:style-name="Tabelle2.D"/>
        <table:table-column table:style-name="Tabelle2.E"/>
        <table:table-column table:style-name="Tabelle2.F"/>
        <table:table-column table:style-name="Tabelle2.G"/>
        <table:table-column table:style-name="Tabelle2.H"/>
        <table:table-row>
          <table:table-cell table:style-name="Tabelle2.A1" table:number-columns-spanned="4" office:value-type="string">
            <text:p text:style-name="P22">SV-An/Abmeldedaten</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Tabelle2.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P4">Bankverbindung</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P4">LehrerCode</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P3"><xsl:value-of select="lektor_kurzbz" /></text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P25">Anschrift:</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P6"><xsl:value-of select="strasse" /></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P10">SVNr</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P6"><xsl:value-of select="svnr" /></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P10">Institut</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P6"><xsl:value-of select="bank_bezeichnung" /></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P10">PersonalNr</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P7"><xsl:value-of select="personalnummer" /></text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P2"/>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P6"><xsl:value-of select="plz" /> <xsl:value-of select="ort" /></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P5"/>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P5"/>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P10">BIC,IBAN</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P6"><xsl:value-of select="bank_bic" /> <xsl:value-of select="bank_iban" /></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P10">Konto</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="P6"><xsl:value-of select="wawi_konto" /></text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P17"/>
      <table:table table:name="Tabelle4" table:style-name="Tabelle4">
        <table:table-column table:style-name="Tabelle4.A"/>
        <table:table-column table:style-name="Tabelle4.B"/>
        <table:table-column table:style-name="Tabelle4.C"/>
        <table:table-column table:style-name="Tabelle4.D"/>
        <table:table-column table:style-name="Tabelle4.E"/>
        <table:table-row>
          <table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
            <text:p text:style-name="P26">An-/Abmeldung</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
            <text:p text:style-name="P32"><xsl:value-of select="anmeldedatum" /></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
            <text:p text:style-name="P31"><xsl:value-of select="abmeldedatum" /></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
            <text:p text:style-name="P27">DVArt</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
            <text:p text:style-name="P32"><xsl:value-of select="dv_art" /></text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row>
          <table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
            <text:p text:style-name="P29"/>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
            <text:p text:style-name="P30"/>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
            <text:p text:style-name="P31"/>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
            <text:p text:style-name="P27"><!--Teiler--></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
            <text:p text:style-name="P32"><!--<xsl:value-of select="teiler" />--></text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P20"/>
      <text:p text:style-name="P23">Lehrveranstaltungstermine mit Honorarsatz</text:p>
      <text:section text:style-name="Sect1" text:name="Spaltenbereich">
        <text:p text:style-name="P23"/>
        <table:table table:name="Termine" table:style-name="Termine">
          <table:table-column table:style-name="Termine.A"/>
          <table:table-column table:style-name="Termine.B"/>
          <table:table-column table:style-name="Termine.C"/>
          <table:table-column table:style-name="Termine.D"/>
          <table:table-column table:style-name="Termine.E"/>
          <table:table-column table:style-name="Termine.F"/>
          <table:table-column table:style-name="Termine.G"/>
          <table:table-row>
            <table:table-cell table:style-name="Termine.A1" office:value-type="string">
              <text:p text:style-name="P11">LE-ID</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.B1" office:value-type="string">
              <text:p text:style-name="P11">Termin</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.B1" office:value-type="string">
              <text:p text:style-name="P11">Von</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.B1" office:value-type="string">
              <text:p text:style-name="P11">Bis</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.B1" office:value-type="string">
              <text:p text:style-name="P11">LE</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.B1" office:value-type="string">
              <text:p text:style-name="P11">Hon.</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.G1" office:value-type="string">
              <text:p text:style-name="P11">VertragNr</text:p>
            </table:table-cell>
          </table:table-row>
			<xsl:apply-templates select="termine"/>
			<!-- Letzte Row Zeichnen damit unten ein Rahmen ist -->
          <table:table-row>
            <table:table-cell table:style-name="Termine.ALast" office:value-type="string">
              <text:p text:style-name="P11"></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.BLast" office:value-type="string">
              <text:p text:style-name="P11"></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.BLast" office:value-type="string">
              <text:p text:style-name="P11"></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.BLast" office:value-type="string">
              <text:p text:style-name="P11"></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.BLast" office:value-type="string">
              <text:p text:style-name="P11"></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.BLast" office:value-type="string">
              <text:p text:style-name="P11"></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.GLast" office:value-type="string">
              <text:p text:style-name="P11"></text:p>
            </table:table-cell>
          </table:table-row>
        </table:table>
        <text:p text:style-name="P16"/>
        <text:p text:style-name="P28">Lehrveranstaltungen mit Summe der LE und Gesamthonorar</text:p>
        <table:table table:name="Lehrveranstaltungen" table:style-name="Lehrveranstaltungen">
          <table:table-column table:style-name="Lehrveranstaltungen.A"/>
          <table:table-column table:style-name="Lehrveranstaltungen.B"/>
          <table:table-column table:style-name="Lehrveranstaltungen.C"/>
          <table:table-column table:style-name="Lehrveranstaltungen.D"/>
          <table:table-column table:style-name="Lehrveranstaltungen.E"/>
          <table:table-row>
            <table:table-cell table:style-name="Lehrveranstaltungen.A1" office:value-type="string">
              <text:p text:style-name="P12">UnterrichtsNr</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.B1" office:value-type="string">
              <text:p text:style-name="P12">Lehrveranstaltung</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.B1" office:value-type="string">
              <text:p text:style-name="P12">Std</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.B1" office:value-type="string">
              <text:p text:style-name="P12">Hon.</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.E1" office:value-type="string">
              <text:p text:style-name="P12">Ges. Hon.</text:p>
            </table:table-cell>
          </table:table-row>
			<xsl:apply-templates select="lehrauftrag"/>

		<!-- Letzte Row Zeichnen damit unten ein Rahmen ist -->
          <table:table-row>
            <table:table-cell table:style-name="Lehrveranstaltungen.ALast" office:value-type="string">
              <text:p text:style-name="P9"></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.BLast" office:value-type="string">
              <text:p text:style-name="P9"></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.BLast" office:value-type="string">
              <text:p text:style-name="P9"></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.BLast" office:value-type="string">
              <text:p text:style-name="P9"></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.ELast" office:value-type="string">
              <text:p text:style-name="P9"></text:p>
            </table:table-cell>
          </table:table-row>
        </table:table>
        <xsl:if test="selbststudium">
            <text:p text:style-name="P16"/>
            <text:p text:style-name="P28">Folgende Anteile am Selbststudium sind im Gesamthonorar enthalten:</text:p>
            <table:table table:name="Lehrveranstaltungen" table:style-name="Lehrveranstaltungen">
              <table:table-column table:style-name="Lehrveranstaltungen.A"/>
              <table:table-column table:style-name="Lehrveranstaltungen.B"/>
              <table:table-column table:style-name="Lehrveranstaltungen.C"/>
              <table:table-column table:style-name="Lehrveranstaltungen.D"/>
              <table:table-column table:style-name="Lehrveranstaltungen.E"/>
              <table:table-row>
                <table:table-cell table:style-name="Lehrveranstaltungen.A1" office:value-type="string">
                  <text:p text:style-name="P12">UnterrichtsNr</text:p>
                </table:table-cell>
                <table:table-cell table:style-name="Lehrveranstaltungen.B1" office:value-type="string">
                  <text:p text:style-name="P12">Lehrveranstaltung</text:p>
                </table:table-cell>
                <table:table-cell table:style-name="Lehrveranstaltungen.B1" office:value-type="string">
                  <text:p text:style-name="P12">Std</text:p>
                </table:table-cell>
                <table:table-cell table:style-name="Lehrveranstaltungen.B1" office:value-type="string">
                  <text:p text:style-name="P12">Hon.</text:p>
                </table:table-cell>
                <table:table-cell table:style-name="Lehrveranstaltungen.E1" office:value-type="string">
                  <text:p text:style-name="P12">Ges. Hon.</text:p>
                </table:table-cell>
              </table:table-row>
    			<xsl:apply-templates select="selbststudium"/>

    		<!-- Letzte Row Zeichnen damit unten ein Rahmen ist -->
              <table:table-row>
                <table:table-cell table:style-name="Lehrveranstaltungen.ALast" office:value-type="string">
                  <text:p text:style-name="P9"></text:p>
                </table:table-cell>
                <table:table-cell table:style-name="Lehrveranstaltungen.BLast" office:value-type="string">
                  <text:p text:style-name="P9"></text:p>
                </table:table-cell>
                <table:table-cell table:style-name="Lehrveranstaltungen.BLast" office:value-type="string">
                  <text:p text:style-name="P9"></text:p>
                </table:table-cell>
                <table:table-cell table:style-name="Lehrveranstaltungen.BLast" office:value-type="string">
                  <text:p text:style-name="P9"></text:p>
                </table:table-cell>
                <table:table-cell table:style-name="Lehrveranstaltungen.ELast" office:value-type="string">
                  <text:p text:style-name="P9"></text:p>
                </table:table-cell>
              </table:table-row>
            </table:table>
        </xsl:if>
        <text:p text:style-name="P16"/>
        <text:p text:style-name="P28">Sonderhonorare</text:p>
        <table:table table:name="Sonderhonorare" table:style-name="Sonderhonorare">
          <table:table-column table:style-name="Sonderhonorare.A"/>
          <table:table-column table:style-name="Sonderhonorare.B"/>
          <table:table-column table:style-name="Sonderhonorare.C"/>
          <table:table-row>
            <table:table-cell table:style-name="Sonderhonorare.A1" office:value-type="string">
              <text:p text:style-name="P12">Datum</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Sonderhonorare.B1" office:value-type="string">
              <text:p text:style-name="P12">Bezeichnung</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Sonderhonorare.C1" office:value-type="string">
              <text:p text:style-name="P12">Ges. Hon.</text:p>
            </table:table-cell>
          </table:table-row>
			<xsl:apply-templates select="sonderhonorar"/>
			<!-- Letzte Row Zeichnen damit unten ein Rahmen ist -->
          <table:table-row>
            <table:table-cell table:style-name="Sonderhonorare.ALast" office:value-type="string">
              <text:p text:style-name="P9"></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Sonderhonorare.BLast" office:value-type="string">
              <text:p text:style-name="P9"></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Sonderhonorare.CLast" office:value-type="string">
              <text:p text:style-name="P9"></text:p>
            </table:table-cell>
          </table:table-row>
        </table:table>
        <text:p text:style-name="P16"/>
        <text:p text:style-name="P15"/>
      </text:section>
      <text:p text:style-name="P19"/>
      <text:p text:style-name="P21">Stand vom <xsl:value-of select="datum_aktuell" /></text:p>

		<xsl:if test="position() != last()">
	      <text:p text:style-name="PageBreak"/>
		</xsl:if>
    </office:text>
</xsl:template>
<xsl:template match="termine">
          <table:table-row>
            <table:table-cell table:style-name="Termine.A2" office:value-type="string">
              <text:p text:style-name="P8"><xsl:value-of select="lehreinheit_id" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.B2" office:value-type="string">
              <text:p text:style-name="P8"><xsl:value-of select="datum" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.B2" office:value-type="string">
              <text:p text:style-name="P8"><xsl:value-of select="von" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.B2" office:value-type="string">
              <text:p text:style-name="P8"><xsl:value-of select="bis" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.B2" office:value-type="string">
              <text:p text:style-name="P8"><xsl:value-of select="einheiten" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.B2" office:value-type="string">
              <text:p text:style-name="P8"><xsl:value-of select="honorar" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Termine.G2" office:value-type="string">
              <text:p text:style-name="P8"><xsl:value-of select="vertragsnummer" /></text:p>
            </table:table-cell>
          </table:table-row>
</xsl:template>
<xsl:template match="lehrauftrag">
          <table:table-row>
            <table:table-cell table:style-name="Lehrveranstaltungen.A2" office:value-type="string">
              <text:p text:style-name="P9"><xsl:value-of select="lehreinheit_id" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.B2" office:value-type="string">
              <text:p text:style-name="P9"><xsl:value-of select="bezeichnung" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.B2" office:value-type="string">
              <text:p text:style-name="P9"><xsl:value-of select="semesterstunden" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.B2" office:value-type="string">
              <text:p text:style-name="P9"><xsl:value-of select="stundensatz" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.E2" office:value-type="string">
              <text:p text:style-name="P9"><xsl:value-of select="gesamt" /></text:p>
            </table:table-cell>
          </table:table-row>
</xsl:template>

<xsl:template match="selbststudium">
          <table:table-row>
            <table:table-cell table:style-name="Lehrveranstaltungen.A2" office:value-type="string">
              <text:p text:style-name="P9"><xsl:value-of select="lehreinheit_id" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.B2" office:value-type="string">
              <text:p text:style-name="P9"><xsl:value-of select="bezeichnung" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.B2" office:value-type="string">
              <text:p text:style-name="P9"><xsl:value-of select="semesterstunden" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.B2" office:value-type="string">
              <text:p text:style-name="P9"><xsl:value-of select="stundensatz" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.E2" office:value-type="string">
              <text:p text:style-name="P9"><xsl:value-of select="gesamt" /></text:p>
            </table:table-cell>
          </table:table-row>
</xsl:template>

<xsl:template match="sonderhonorar">
          <table:table-row>
            <table:table-cell table:style-name="Sonderhonorare.A2" office:value-type="string">
              <text:p text:style-name="P9"><xsl:value-of select="datum" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Sonderhonorare.B2" office:value-type="string">
              <text:p text:style-name="P9"><xsl:value-of select="bezeichnung" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Sonderhonorare.C2" office:value-type="string">
              <text:p text:style-name="P9"><xsl:value-of select="gesamt" /></text:p>
            </table:table-cell>
          </table:table-row>
</xsl:template>
</xsl:stylesheet>
