#!/usr/bin/env python

import sys
import os
import re
import shutil
import codecs

from optparse import OptionParser

CONFIGS = { "Fire" : { "enable-divisions": False,
                       "enable-bare-sections": True,
                       "section-re": 1 },
            "BIC Codes": { "enable-divisions": False,
                           "enable-subsections": True,
                           "section-re": 1 },
            "ELECTRICAL": { "enable-divisions": False,
                            "enable-subsections": True,
                            "section-re": 0,
                            "article-re": 1 },
            "HOUSING": { "enable-divisions": False,
                         "enable-subsections": True,
                         "section-re": 0 },
            "Health" : { "enable-divisions": False },
            "Planning" : { "article-re": 2 },
            "Planning.txt" : { "article-re": 2 }
            }


def countIndent(indent):
    count = 0
    for c in indent:
        if c =='\t':
            count += 2
        else:
            count += 1
    return count


def isRomanNumeral(s):
    m = re.match( ur'(?ui)^[ivx]+$', s )
    if m:
        print "isRoman, s = %s, returning True" % ( s )
        return True
    print "isRoman, s = %s, returning False" % ( s )
    return False

def romanValue(c):
    if c == 'i':
        return 1
    elif c == 'v':
        return 5
    elif c == 'x':
        return 10
    elif c == 'l':
        return 50
    elif c == 'c':
        return 100
    elif c == 'd':
        return 500
    elif c == 'm':
        return 1000
    raise Exception( "romanValue: Unknown numeral: " + c )

def parseRomanNumeral(r):
    print "parseRomanNumeral r = ", r
    r = r.lower()
    val = 0
    prevc = None
    comps = []
    for c in r:
        comp = None
        if prevc:
            pair = prevc + c
            #print "pair = ",pair
            if pair in ['iv', 'ix', 'xl', 'xc', 'cd', 'cm']:
                comp = pair
        if comp:
            comps.append(comp)
            prevc = None
        else:
            if prevc:
                comps.append(prevc)
            prevc = c

    if prevc:
        comps.append(prevc)

    print "parseRomanNumeral comps: ",str(comps)

    for comp in comps:
        if comp == 'iv':
            val += 4
        elif comp == 'ix':
            val += 9
        elif comp == 'xl':
            val += 40
        elif comp == 'xc':
            val += 90
        elif comp == 'cd':
            val += 400
        elif comp == 'cm':
            val += 900
        else:
            val += romanValue(comp)
    print "parseRomanNumeral val = ", val
    return val

def parseAlpha(valstr):
    alpha = valstr.lower()
    aval = 0
    for i in range(len(alpha)):
        expval = len(alpha) - i - 1
        c = alpha[i]
        cval = ord(c) - ord('a')
        aval += cval * (26**expval)
    return aval

def convertToOrdinal(s):
    try:
        val = int(s)
        return val
    except:
        pass
    if isRomanNumeral(s):
        return parseRomanNumeral(s)
    return parseAlpha(s)

def orderByValue(valstr):
    m = re.match(ur'(?iu)(\d+)([A-Za-z]*)', valstr)
    val = 0
    if m:
        ival = int(m.group(1))
        alpha = m.group(2).lower()
        aval = 0
        if len (alpha) > 0:
            for i in range(len(alpha)):
                expval = len(alpha) - i - 1
                c = alpha[i]
                cval = ord(c) - ord('a')
                aval += cval * (26**expval)
        val = ival + aval
    return val

def parseIdentifierComponent( comp ):
    maxval = 0
    try:
        val = int( comp )
        return val
    except:
        pass
    if isRomanNumeral( comp ):
        val = parseRomanNumeral( comp )
    else:
        val = parseAlpha( comp )
    return val

def splitIdentifier( identifier ):
    print "splitIdentifier: identifier = ", identifier
    nchars = len( identifier )
    comps = []
    currentComp = ''
    prevc = ''
    for i in range( nchars ):
        c = identifier[i]
        print "c = ", c
        print "prevc = ", prevc
        print "currentComp = ", currentComp
        split = False
        keep = True
        if c in [ '.', '-', '(', ')' ]:
            split = True
            keep = False
        elif re.match( r'[A-Za-z]', prevc ) and re.match( r'[0-9]', c ):
            print "Splitting"
            split = True
        elif re.match( r'[0-9]', prevc ) and re.match( r'[A-Za-z]', c ):
            print "Splitting"
            split = True
        if split:
            comps.append( currentComp )
            currentComp = ''
            if keep:
                currentComp += c
        else:
            currentComp += c
        prevc = c
    if currentComp != '':
        comps.append( currentComp )

    return comps

def parseIdentifier(ident):
    print "parseIdentifier: ident = ", ident
    #comps = re.split( ur'[.\(\)-]', ident )
    comps = splitIdentifier( ident )
    ncomps = len( comps )
    print "parseIdentifier: comps = %s" % ( str( comps ) )
    crec = { 'components' : [], 'value': [], 'identifier': ident }
    values = []
    for i in range( ncomps ):
        comp = comps[i]
        val = parseIdentifierComponent( comp )
        print "  comp = %s, val = %s" % ( comp, val )
        crec['components'].append( comp )
        values.append( val )
    crec['values'] = tuple( values )
    return crec

class Container:

    ORDER_BY_LISTS = {}

    ORDER_BY_MAPS = {}

    def __init__( self ):
        self.keyword = ''
        self.title = ''
        self.number = ''
        self.orderby = 0
        self.children = []
        self.parent = None

    def parseNumber( self ):
        print "parseNumber called, number = ", self.number
        if self.number != '':
            self.orderby = parseIdentifier( self.number )['values']
            print "orderby = ", self.orderby
            if self.keyword not in self.ORDER_BY_LISTS:
                self.ORDER_BY_LISTS[self.keyword] = set()
            self.ORDER_BY_LISTS[self.keyword].add( self.orderby )
        for child in self.children:
            if isinstance( child, Container ):
                child.parseNumber()

    def getOrderBy( self ):
        if self.keyword not in self.ORDER_BY_MAPS:
            values = list( self.ORDER_BY_LISTS[self.keyword] )
            values.sort()
            self.ORDER_BY_MAPS[self.keyword] = dict( [ ( values[i], i ) for i in range( len( values ) ) ] )
            print "orderby map: ", self.ORDER_BY_MAPS[self.keyword]
        obval = self.ORDER_BY_MAPS[self.keyword][self.orderby]
        print "number = %s, orderby = %s, obval = %s" % ( self.number, self.orderby, obval )
        return "%04d" % ( obval )

    def addChild( self, container ):
        if isinstance( container, Container ):
            container.parent = self
        self.children.append( container )

    def writeXML( self, dirname ):
        """Default implementation - override for specific containers"""
        print "Container.writeXML: title = %s, number children = %s" % ( self.title, len( self.children ) )
        for child in self.children:
            child.writeXML( dirname )

class Code(Container):

    def __init__( self ):
        Container.__init__( self )
        self.keyword = "CODE"

    def getOrderBy( self ):
        return 0

class Section(Container):

    def __init__( self, config ):
        Container.__init__( self )
        self.config = config
        self.keyword = 'SECTION'

    def getNumberStructures( self ):
        count = 0
        cont = self
        while cont.parent and ( cont.parent.keyword != 'CODE' ):
            cont = cont.parent
            count += 1
        return count

    def getStructure1( self ):
        nstructs = self.getNumberStructures()
        struct = self.parent
        if nstructs > 1:
            struct = struct.parent
        return { 'label': struct.keyword,
                 'identifier': struct.number,
                 'title': struct.title,
                 'level':      1,
                 'order_by': struct.getOrderBy() }

    def getStructure2( self ):
        level = 1
        nstructs = self.getNumberStructures()
        if nstructs > 1:
            level = 2
        struct = self.parent
        return { 'label': struct.keyword,
                 'identifier': struct.number,
                 'title': struct.title,
                 'level':      level,
                 'order_by': struct.getOrderBy() }


    def writeXML( self, dirname ):
        nstructs = self.getNumberStructures()
        srec = self.getStructure1()
        parentNumber = srec['identifier']
        secid = parentNumber + "_" + self.number
        filename = os.path.join( dirname, '%s.xml' % ( secid ) )
        ofile = codecs.open( filename, encoding='utf-8', mode='w' )
        indentStep = "     "
        indent = indentStep
        indents = []
        ofile.write('<?xml version="1.0" encoding="utf-8"?>\n')
        ofile.write('<law>\n')
        ofile.write('%s<structure>\n' % ( indent ) )
        indents.append(indent)
        indent += indentStep
        ofile.write('%s<unit label="%s" identifier="%s" order_by="%s" level="%d">%s</unit>\n' % ( indent,
                                                                                                  srec['label'],
                                                                                                  srec['identifier'],
                                                                                                  srec['order_by'],
                                                                                                  srec['level'],
                                                                                                  srec['title'] ) )
        if nstructs > 1:
            srec = self.getStructure2()
            ofile.write('%s<unit label="%s" identifier="%s" order_by="%s" level="%d">%s</unit>\n' % ( indent,
                                                                                                      srec['label'],
                                                                                                      srec['identifier'],
                                                                                                      srec['order_by'],
                                                                                                      srec['level'],
                                                                                                      srec['title'] ) )
            parentNumber = srec['identifier']

        indent = indents.pop()
        ofile.write('%s</structure>\n' % ( indent ) )
        ofile.write('%s<section_number>%s</section_number>\n' % ( indent, secid ) )
        ofile.write('%s<catch_line>%s</catch_line>\n' % ( indent, self.title ) )
        ofile.write('%s<order_by>%s</order_by>\n' % ( indent, self.getOrderBy() ) )
        ofile.write('%s<text>\n' % (indent) )
        indents.append( indent )
        indent += indentStep
        for line in self.children:
            sub = False
            if self.config and ( 'enable-subsections' in self.config ) and self.config['enable-subsections']:
                sub = True
                m = CodeParser.BARE_SECTION_RE.match( line )
                if m:
                    prefix = m.group( 1 )
                    text = m.group( 2 )
                    ofile.write('%s<section prefix="%s">\n' % ( indent, prefix ) )
                else:
                    ofile.write('%s<section prefix="%s">\n' % ( indent, "" ) )
            ofile.write('%s%s\n' % ( indent, line ) )
            if sub:
                ofile.write('%s</section>\n' % ( indent ) )
        indent = indents.pop()
        ofile.write('%s</text>\n' % (indent) )

        ofile.write('</law>\n')


class CodeParser:

    PREFACE_RE = re.compile( ur'PREFACE.*' )

    CODE_RE = re.compile( ur'(?u)^([A-Za-z ]+)\s*CODE\s*$' )

    DIVISION_RE = re.compile( ur'(?u)^DIVISION\s+([^:\s]+)\s*:?\s*$' )

    ARTICLE_RE = [ re.compile( ur'(?iu)^ARTICLE\s+([^:]+)\s*:?\s*$' ),
                   re.compile( ur'(?u)^ARTICLE\s*([0-9][0-9A-Z.]*)\s*[-\u2013]\s*(.*)\s*$' ),
                   re.compile( ur'(?u)^ARTICLE\s+([0-9][0-9A-Z.]*)\s*:?\s*$' ), ]

    CHAPTER_RE = re.compile( ur'(?iu)^CHAPTER\s+([^:]{1,10})\s*:?\s*$' )

    #SECTION_RE = re.compile( ur'(?u)^SEC\.\s*(\S+)\.\s*(.*)\s*$' )

    SECTION_RE = [ re.compile( ur'(?u)^(?:SEC\.\s*)?((?:[0-9][0-9A-Z]*\.)*[0-9][0-9A-Z]*(?:-[0-9]*)?)\.?\s*(.*)\s*$' ),
                   re.compile( ur'(?u)^SECTION\s*([0-9][0-9A-Z.]*)\s*[-\u2013]\s*(.*)\s*$' ) ]

    BARE_SECTION_RE = re.compile( ur'^((?:[0-9][0-9A-Z]*\.)*[0-9][0-9A-Z]*)\.?\s*(.*)\s*$' )

    previous_articles = []

    def __init__( self, config = None ):
        self.doc = None
        self.currentLine = ''
        self.lines = []
        self.codes = []
        self.currentCode = None
        self.currentContainer = None
        self.currentSection = None
        self.currentLineIndex = 0
        self.lastSectionBare = False
        self.sectionRe = self.SECTION_RE[0]
        self.articleRe = self.ARTICLE_RE[0]
        self.enableDivisions = True
        self.enableBareSections = False
        self.updateConfig( config )

    def updateConfig( self, config ):
        self.config = config
        if config:
            if "section-re" in config:
                self.sectionRe = self.SECTION_RE[ config["section-re"] ]
            if "article-re" in config:
                self.articleRe = self.ARTICLE_RE[ config["article-re"] ]
            if "enable-divisions" in config:
                self.enableDivisions = config["enable-divisions"]
            if "enable-bare-sections" in config:
                self.enableBareSections = config["enable-bare-sections"]

    def parse( self, doc ):
        for c in doc:
            #print "c = %s (%s)" % ( c, ord(c) )
            # Fix mis-coded en dash
            if c == u'\u00F1':
                #print "Mis-code found"
                c = u'\u2013'
            elif c in [ u'\u00EC', u'\u00EE' ]:
                c = '"'
            if c == '\n':
                #print self.currentLine
                self.lines.append( self.currentLine )
                self.currentLine = ''
            else:
                self.currentLine += c

        # for line in self.lines:
        #     print "line: ", line
        #     print
        self.parseLines()

    def parseLines( self ):
        self.lastSectionBare = False
        self.currentLineIndex = 0
        while self.currentLineIndex < len( self.lines ):
            self.parseLine()

    def parseLine( self ):
        line = self.lines[ self.currentLineIndex ]
        print "line: ", line
        if re.match( ur'^References to Ordinances\s*$', line ):
            self.currentLineIndex = len( self.lines )
            return
        if re.match( ur'^432 Walnut Street, Suite 1200\s*$', line ):
            self.currentLineIndex += 1
            return
        if re.match( ur'^1996 CHARTER\s*$', line ):
            codeName = "CHARTER"
            self.currentSection = None
            self.currentCode = Code()
            self.currentCode.title = codeName
            container = self.currentContainer
            if container and ( container.keyword == 'CODE' ):
                container = container.parent
            if container:
                container.addChild( self.currentCode )
            self.currentContainer = self.currentCode
            self.codes.append( self.currentCode )
            print "Code: %s" % ( codeName )
            self.currentLineIndex += 1
            return
        m = self.CHAPTER_RE.match( line )
        if m:
            self.currentSection = None
            chapterNumber = m.group( 1 )
            chapterName = self.lines[ self.currentLineIndex + 1 ]
            chapter = Container()
            chapter.keyword = 'Chapter'
            chapter.number = chapterNumber
            chapter.title = chapterName
            if not self.currentContainer:
                raise Exception( "Chapter encountered with no current container" )
            container = self.currentContainer
            if self.currentContainer.keyword == 'Chapter':
                container = container.parent
            container.addChild( chapter )
            self.currentContainer = chapter
            print "Chapter: %s - %s" % ( chapterNumber, chapterName )
            self.currentLineIndex += 2
            return
        m = self.articleRe.match( line )
        if m and m not in self.previous_articles:
            print self.previous_articles
            if not self.articleRe.match( self.lines[ self.currentLineIndex + 1 ] ):

                self.previous_articles.append(m);

                self.currentSection = None
                articleNumber = filter( lambda x: x != "*", m.group( 1 ) )
                if self.config and ( "article-re" in self.config ) and ( self.config["article-re"] == 1 ):
                    articleName = m.group( 2 )
                    linedelta = 1
                else:
                    articleName = self.lines[ self.currentLineIndex + 1 ]
                    linedelta = 2
                article = Container()
                article.keyword = 'Article'
                article.number = articleNumber
                article.title = articleName
                if not self.currentContainer:
                    raise Exception( "Article encountered with no current container" )
                container = self.currentContainer
                if self.currentContainer.keyword == 'Article':
                    container = container.parent
                container.addChild( article )
                self.currentContainer = article
                print "Article: %s - %s" % ( articleNumber, articleName )
                self.currentLineIndex += linedelta
                return
        m = self.DIVISION_RE.match( line )
        if m and self.enableDivisions:
            if not re.match( ur'^Article\s*$', self.lines[ self.currentLineIndex - 1 ] ):
                #print "nextline: ", self.lines[ self.currentLineIndex + 1 ]
                self.currentSection = None
                divisionNumber = m.group( 1 )
                divisionName = self.lines[ self.currentLineIndex + 1 ]
                division = Container()
                division.keyword = 'Division'
                division.number = divisionNumber
                division.title = divisionName
                if not self.currentContainer:
                    raise Exception( "Division encountered with no current container" )
                container = self.currentContainer
                if self.currentContainer.keyword == 'Division':
                    container = container.parent
                container.addChild( division )
                self.currentContainer = division
                print "Division: %s - %s" % ( divisionNumber, divisionName )
                self.currentLineIndex += 2
                return
        m = self.sectionRe.match( line )
        bm = self.BARE_SECTION_RE.match( line )
        if m or ( self.enableBareSections and ( bm and ( ( not self.currentSection ) or self.lastSectionBare ) ) ):
            if not m:
                self.lastSectionBare = True
                m = bm
            else:
                self.lastSectionBare = False
            m2 =  self.sectionRe.match( self.lines[ self.currentLineIndex - 1 ] )
            if m2 and m.group( 1 ) == m2.group( 1 ):
                print "Adding line to current section"
                self.currentSection.addChild( line )
                self.currentLineIndex += 1
                return
            sectionNumber = m.group( 1 )
            sectionName = m.group( 2 )
            print "Section: %s - %s" % ( sectionNumber, sectionName )
            section = Section( self.config )
            section.number = sectionNumber
            section.title = sectionName
            if not self.currentContainer:
                raise Exception( "Section encountered with no current container" )
            self.currentContainer.addChild( section )
            self.currentSection = section
            #self.currentContainer = section
            self.currentLineIndex += 1
            return
        m = self.CODE_RE.match( line )
        if m:
            codeName = m.group( 1 ).strip()
            #print "codeName = ", codeName
            if ( ( codeName not in [ 'MUNICIPAL', 'FORMER' ] ) and
                 ( not self.PREFACE_RE.match( self.lines[self.currentLineIndex-1] ) ) and
                 ( not re.match( ur'^ATTACHMENT.*', self.lines[self.currentLineIndex-1] ) ) and
                 ( not re.match( ur'^EXCERPT.*', self.lines[self.currentLineIndex] ) ) and
                 ( not re.match( ur'^PREFACE.*', self.lines[self.currentLineIndex] ) ) ):
                self.currentSection = None
                self.currentCode = Code()
                self.currentCode.title = codeName
                if codeName in CONFIGS:
                    self.updateConfig( CONFIGS[codeName] )
                # container = self.currentContainer
                # if container and ( container.keyword == 'CODE' ):
                #     container = container.parent
                # if container:
                #     container.addChild( self.currentCode )
                self.currentContainer = self.currentCode
                self.codes.append( self.currentCode )
                print "Code: %s" % ( codeName )
            self.currentLineIndex += 1
            return
        if re.match( ur'^CODIFICATION NOTE\s*$', line ):
            self.currentLineIndex += 1
            while re.match( ur'^\d+\..*$', self.lines[ self.currentLineIndex ] ):
                self.currentLineIndex += 1
            return
        if self.currentSection:
            # Add lines to current section
            self.currentSection.addChild( line )

        self.currentLineIndex += 1

def processFile( path, xmldir ):
    print "processFile: path = ", path
    fname = os.path.basename( path )
    config = None
    if fname in CONFIGS:
        config = CONFIGS[fname]
    parser = CodeParser( config )
    ifile = file( path )
    doc = unicode( ifile.read(), 'latin-1' )
    ifile.close()
    parser.parse( doc )
    print "After parsing"
    print "Number codes: ", len( parser.codes )
    for code in parser.codes:
        print "Calling parseNumber"
        code.parseNumber()
    if len( parser.codes ) > 1:
        for code in parser.codes:
            codedir = os.path.join( xmldir, code.title )
            if os.path.exists( codedir ):
                shutil.rmtree( codedir )
            os.mkdir( codedir )
            print "Calling writeXML for code: %s, codedir = %s" % ( code.title, codedir )
            code.writeXML( codedir )
    else:
        code = parser.codes[0]
        code.writeXML( xmldir )

if __name__ == "__main__":

    oparser = OptionParser()

    oparser.add_option("-i",
                       "--input-file",
                       dest="inputFile",
                       help="Single title html file to parse (optional)")

    oparser.add_option("-x",
                       "--xml-directory",
                       dest="xmlDir",
                       help="Directory for xml output files")


    (options, args) = oparser.parse_args()

    if not options.inputFile:
        print "ERROR: Input file must be specified"
        sys.exit( -1 )

    if not options.xmlDir:
        print "ERROR: XML Directory must be specified"
        sys.exit( -1 )

    if os.path.exists( options.xmlDir ):
        shutil.rmtree( options.xmlDir )

    if not os.path.exists( options.xmlDir ):
        os.mkdir( options.xmlDir )

    processFile( options.inputFile, options.xmlDir )

