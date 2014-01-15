#!/usr/bin/env python

import sys
import os
import re
import shutil
import codecs

from HTMLParser import HTMLParser

from optparse import OptionParser

#EXCLUDED_FILES = frozenset( [ 'title04.html', 'title04.01.html', ' title04.02.html' ] ) 
EXCLUDED_FILES = frozenset() 

#IDENTIFIER_PATTERN = ur'[0-9a-z.-\(\)]+'

#PARAGRAPH_PATTERN = ur'(?umi)^(\s*)(?:\u00A7|(?:section))?\s*[^0-9a-zA-Z]?((?:[a-z]+-)?(?:[a-z])?(?:\d+-)*\d+)(\.(?:\d+.)*\d+)?\s*(.*)\s*$'
#PARAGRAPH_PATTERN = ur'(?umi)^(\s*)SEC\.\s*([\d.]+(?:-?\(?[a-z]+\)?)?)\.\s*(.*)\s*$'
PARAGRAPH_PATTERN = ur'(?umi)^(\s*)SEC\.\s*([0-9a-z.\(\)-]+)\.\s*(.*)\s*$'

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
        return True
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

    #print "comps: ",str(comps)

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
        val = parseRomanNumeral(s)
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

def prefixInfo(prefix):
    print "prefixInfo: prefix = ", prefix
    if prefix:
        prefix = prefix.strip('.')
    infos = []
    if (prefix is None) or (len(prefix) == 0):
        infos.append({'type': 'NONE', 'val': 1})
        return infos
    m = re.match(ur'(?iu)^\s*\(?\s*([ivx]+)\s*\)?\s*$', prefix)
    if m:
        val = parseRomanNumeral(m.group(1))
        infos.append({'type': 'ROMAN', 'val': val})
    m = re.match(ur'(?u)^\s*\(?\s*(\d+)(?:[\.-].)?\s*\)?\s*$', prefix)
    if m:
        infos.append({'type': 'NUMERIC', 'val': int(m.group(1))})
    m = re.match(ur'(?u)^\s*\(?\s*([a-zA-Z]){1,3}(?:[.-]?.)?\s*\)?\s*$', prefix)
    if m:
        chars = m.group(1)
        nchars = len(chars)
        if nchars > 1:
            for i in range(nchars):
                lc = m.group(1).lower()
                fact = 26 * (nchars - i - 1)
                if fact == 0:
                    fact = 1
                val += ( ord(lc) - ord('a') ) * fact
        else:
            lc = m.group(1).lower()
            val = ord(lc) - ord('a') + 1
        infos.append({'type': 'ALPHA', 'val': val})
    if len(infos) == 0:
        raise Exception( "prefixInfo: ERROR Failed to parse prefix: " + prefix )
    return infos

def isEditorsNote(paragraph):
    #print "isEditorsNote: paragraph = ",paragraph
    m = re.search( ur"(?mui)Editor('|\u2019)s\s+note\s*\u2013", paragraph)
    #m = re.search( ur"(?mui)Editor's", paragraph)
    if m:
        #print "Returning True"
        return True
    #print "Returning False"
    return False


def parseParagraphHeader(header):
    print "parseParagrapHeader: header = ",header
    #m = re.match( ur'(?umi)^(\s*)(?:\u00A7|(?:section))?\s*[^\d]?((?:[a-z]-)?(?:[a-z])?(?:\d+-)*\d+)(\.(?:\d+.)*\d+)?\s*(.*)\s*$', header )
    m = re.match( PARAGRAPH_PATTERN, header )
    if not m:
        raise Exception( "parseParagraphHeader: Failed to parse: " + header )
    groups = m.groups()
    indent = groups[0]
    if indent is None:
        indent = ''
    lawId = groups[1]
    #sectionsString = groups[2]
    sectionsString = None
    sectionName = groups[2]
    nameType = 'LAW'
    sections = None
    if sectionsString:
        nameType = 'SECTION'
        sections = filter( lambda x: len(x) > 0, sectionsString.split( '.' ) )
    return { 'type' : nameType,
             'indent': countIndent(indent),
             'lawId': lawId, 
             'sections': sections, 
             'sectionName': sectionName }

class Title:

    def __init__(self):
        self.chapters = []
        self.content = ''
        self.currentChapter = None
        self.titleId = None
        self.titleName = None
        self.parsed = False

    def addChapter(self, chapter):
        self.chapters.append(chapter)
        self.currentChapter = chapter

    def dump(self):
        print "Title: %s" % (self.content)
        for chapter in self.chapters:
            chapter.dump()

    def writeXML(self,dirname):
        for chapter in self.chapters:
            chapter.writeXML(dirname)

    def getId(self):
        if not self.parsed:
            self.parse()
        return self.titleId

    def getName(self):
        if not self.parsed:
            self.parse()
        return self.titleName
            
    def parse(self):
        #print "Title.parse, content = ", self.content
        #m = re.match( ur'(?imu)^\s*ARTICLE\s+([.\d]+-?[a-z]*):\s+(.+)\s*$', self.content)
        m = re.match( ur'(?imu)^\s*ARTICLE\s+([0-9a-z-]+):\s+(.+)\s*$', self.content)
        #print "m = ",m
        if m:
            self.titleId = filter( lambda x: x != '.', m.group(1))
            self.titleName = m.group(2)
        self.parsed = True
                      

class Article:

    def __init__(self, chapter):
        self.chapter = chapter
        self.content = ''
        self.laws = []
        self.articleId = None
        self.articleName = None
        self.articleLabel = None
        self.currentLaw = None
        self.parsed = False

    def addLaw(self, law):
        self.laws.append(law)
        self.currentLaw = law

    def dump(self):
        print "Article: %s" % (self.content)
        for law in self.laws:
            chapter.dump()

    def writeXML(self,dirname):
        for law in self.laws:
            law.writeXML(dirname)

    def getId(self):
        if not self.parsed:
            self.parse()
        #print "Article.getId, id = ",self.articleId
        return self.articleId

    def getLabel(self):
        if not self.parsed:
            self.parse()
        return self.articleLabel

    def getName(self):
        if not self.parsed:
            self.parse()
        return self.articleName

    def parse(self):
        m = re.match( ur'(?imu)^\s*Article\s+([ivxlcdm]+)\.\s+(.+)\s*$', self.content)
        if m:
            self.articleId = 'Article-%s' % (m.group(1))
            self.articleLabel = 'article'
            self.articleName = m.group(2)
        else:
            m = re.match( ur'(?imu)^\s*Part\s+(.*)\.\s+(.+)\s*$', self.content)
            if m:
                self.articleId = 'Article-%s' % (m.group(1))
                self.articleLabel = 'part'
                self.articleName = m.group(2)
        self.parsed = True

class Chapter:

    def __init__(self, title):
        self.title = title
        self.articles = []
        self.laws = []
        self.content = ''
        self.currentLaw = None
        self.currentArticle = None
        self.chapterId = None
        self.chapterName = None
        self.parsed = False

    def currentContainer(self):
        if self.currentArticle:
            return self.currentArticle
        return self

    def getId(self):
        if not self.parsed:
            self.parse()
        return self.chapterId

    def getName(self):
        if not self.parsed:
            self.parse()
        return self.chapterName

    def addLaw(self, law):
        self.laws.append(law)
        self.currentLaw = law

    def addArticle(self, article):
        self.articles.append(article)
        self.currentArticle = article

    def writeXML(self,dirname):
        for law in self.laws:
            law.writeXML(dirname)
        for article in self.articles:
            article.writeXML(dirname)

    def dump(self):
        print "  Chapter: %s" % (self.content)
        for law in self.laws:
            law.dump()
        for article in self.articles:
            article.dump()

    def parse(self):
        print "Chapter.parse, content = ", self.content
        m = re.match( ur'(?imu)^\s*(?:CHAPTER|Ch\.)\s+((?:\d+-)*\d+[A-Za-z]*)\.?\s+(.+)\s*$', self.content)
        print "m = ",m
        if m:
            self.chapterId = m.group(1)
            self.chapterName = m.group(2)
        else:
            print "Chapter.parse Failed to parse line: ", self.content
        self.parsed = True

class SectionContainer:

    INITIAL_NONE_SCORE = 0.5

    INITIAL_SUCC_SCORE = 1.0

    SUCC_SCORE = 3.0

    NONE_SCORE = 0.6

    INDENT_MATCH_SCORE = 0.2

    TREE_DIST_PENALTY = -0.2

    INDENT_DIFF_PENALTY = -1.0

    CHILD_OUTDENT_PENALTY = -0.5

    INITIAL_SUCC_PENALTY = -10.0

    SUCC_PENALTY = -10.0

    SUCC_DELTA_PENALTY = -0.5

    def __init__(self):
        self.parent = None
        self.subsections = []
        self.prefix = None
        self.indent = 0
        self.headrec = None

    def succeedingPrefixes(self, p1, p2, strict=False):
        trecs1 = prefixInfo(p1)
        trecs2 = prefixInfo(p2)
        print "succeedingPrefixes: p1 = %s, p2 = %s" % (p1, p2)
        ok = False
        for trec1 in trecs1:
            for trec2 in trecs2:
                print "trec1 = %s, trec2 = %s" % (trec1, trec2)
                if trec1['type'] == trec2['type']:
                    print "types are equal"
                    if ((trec1['type'] == 'NONE') and not strict) or (trec2['val'] == (trec1['val'] + 1)):
                        print "successive values"
                        ok = True
                        break
            if ok:
                break            
        print "succeedingPrefixes, returning: ", ok
        return ok

    def prefixDelta(self, p1, p2):
        trecs1 = prefixInfo(p1)
        trecs2 = prefixInfo(p2)
        mindelta = 1000
        for trec1 in trecs1:
            for trec2 in trecs2:
                if trec1['type'] == trec2['type']:
                    delta = trec2['val'] - trec1['val']
                    if (delta > 0) and (delta < mindelta):
                        mindelta = delta
        return mindelta

    def isValidSubsection(self, section, strict=False):
        print "isValidSubsection Called, len(self.subsections) = ", len(self.subsections)
        ok = False
        if len(self.subsections) > 0:
            last = self.subsections[-1]
            if not self.succeedingPrefixes(last.prefix, section.prefix, strict):
                print "isValidSubsection: Returning False (not successive)"
                return False
            else:
                print "isValidSubsection: Returning True (successive)"
                return True
        else:
            pinfos = prefixInfo(section.prefix)
            ok = False
            for pinfo in pinfos:
                if pinfo['val'] == 1:
                    ok = True
                    break
        print "isValidSubsection: Returning: ", ok
        return ok

    def scoreSubsection(self, section):
        """Return a score for how good a match this section is to be adopted by us"""
        if isinstance(self, TableSection):
            return -1000.0
        content = section.__dict__.get('content')
        mycontent = self.__dict__.get('content')
        print "scoreSubsection: prefix = %s, content = %s, mycontent = %s" % (section.prefix, content, mycontent) 
        score = 0.0
        if len(self.subsections) == 0:
            pinfos = prefixInfo(section.prefix)
            for pinfo in pinfos:
                if (pinfo['type'] == 'NONE'):
                    print "  INITIAL_NONE_SCORE"
                    score += self.INITIAL_NONE_SCORE
                elif pinfo['val'] == 1:
                    print "  INITIAL_SUCC_SCORE"
                    score += self.INITIAL_SUCC_SCORE
                else:
                    print "  INITIAL_SUCC_PENALTY"
                    score += self.INITIAL_SUCC_PENALTY
            if section.indent > self.indent:
                print "  CHILD_OUTDENT_PENAALTY"
                score += self.CHILD_OUTDENT_PENALTY
            print "  returning score = ", score
            return score
        last = self.subsections[-1]
        if (not section.prefix) and (not last.prefix):
            print "  NONE_SCORE"
            score += self.NONE_SCORE
        elif self.succeedingPrefixes(last.prefix, section.prefix, True):
            print "  SUCC_SCORE"
            score += self.SUCC_SCORE
        else:
            delta = self.prefixDelta(last.prefix, section.prefix)
            score += delta * self.SUCC_DELTA_PENALTY
        idelta = abs(last.indent - section.indent)
        print "  idelta = ", idelta
        score += self.INDENT_DIFF_PENALTY * idelta
        if idelta == 0:
            score += self.INDENT_MATCH_SCORE
        print "  returning score = ", score
        return score

    def addSubsection(self, section,force=False):
        print "addSubsection Called, len(self.subsections) = ", len(self.subsections)
        if (not force) and (not self.isValidSubsection(section)):
            if len(self.subsections) > 0:
                last = self.subsections[-1]
                msg = "Section.addSubsection Not successive prefixes: %s and %s" % (last.prefix, section.prefix)
            else:
                msg = "Section.addSubsection Not initial prefix: %s" % (section.prefix)
            #raise Exception(msg)
        section.parent = self
        self.subsections.append(section)
        print "addSubsection: parent prefix = %s, prefix = %s" % (self.prefix, section.prefix)

    def scoreCompatibleContainers(self, section, penalty, visited=None):
        if visited is None:
            visited = set()
        containers = []
        containers.append({'container': self, 'score': self.scoreSubsection(section)+penalty})
        visited.add(self)
        if self.parent in visited:
            raise Exception( "scoreCompatibleContainers: Loop detected" )
        if self.parent:
            containers += self.parent.scoreCompatibleContainers(section, penalty+self.TREE_DIST_PENALTY, visited)
        return containers

    def findBestContainer(self, section):
        content = section.__dict__.get('content')
        containers = self.scoreCompatibleContainers(section, 0.0)
        if len(containers) == 0:
            return None
        containers.sort(key = lambda x: x['score'], reverse=True)
        topscore = containers[0]['score']
        print "findBestContainer: section content = ", content
        print "findBestContainer: best score = %d, num containers = %d" % (topscore, len(containers))
        print "Containers:"
        for c in containers:
            cnt = c['container']
            content = None
            try:
                content = cnt.content
            except:
                pass
            print "  prefix=%s, score=%s, content=%s" % (cnt.prefix, c['score'], content)
        # if topscore < 0.0:
        #     return None
        return containers[0]['container']

    def findContainerByPrefix(self, section):
        print "findContainerByPrefix: my prefix = %s" % (self.prefix)
        if (len( self.subsections ) < 1):
            if self.parent:
                return self.parent.findContainerByPrefix(section)
            else:
                return None
        lastsection = self.subsections[-1]
        print "findContainerByPrefix: section.prefix=%s, lastsection.prefix=%s" % (section.prefix, lastsection.prefix)
        if self.succeedingPrefixes(lastsection.prefix, section.prefix):
            return self
        if not self.parent:
            return None
        return self.parent.findContainerByPrefix(section)


class Section(SectionContainer):

    def __init__(self):
        SectionContainer.__init__(self)
        self.content = ''

    def findIndentLevel(self, indent):
        if indent == self.indent:
            return self
        if ( self.parent is None ) or ( indent > self.indent ):
            return None
        if self.parent:
            return self.parent.findIndentLevel(indent)

    def writeXML(self, ofile, indent):
        if self.prefix:
            prefix = self.prefix
        else:
            prefix = ''
        ofile.write('%s<section prefix="%s">\n' % (indent, prefix))
        ofile.write('%s\n' % self.content)
        nextindent = indent + "  "
        for sub in self.subsections:
            sub.writeXML(ofile, nextindent)
        ofile.write('%s</section>\n' % (indent))

class TableSection(Section):

    def __init__(self, table):
        Section.__init__(self)
        print "TableSection created"
        self.table = table

    def writeXML(self, ofile, indent):
        ofile.write('%s<section prefix="" type="table">\n' % (indent))
        header = self.table.header
        if header:
            for cell in header:
                ofile.write('%s%20s' % (indent, cell.strip()))
            ofile.write('\n')
        for row in self.table.rows:
            for cell in row:
                print "TableSection.writeXML cell = %s, len(cell) = %d" % (cell.strip(), len(cell.strip()))
                ofile.write('%s%20s' % (indent, cell.strip()))
            ofile.write('\n')
        ofile.write('%s</section>\n' % (indent))

class Law(SectionContainer):

    LAW_IDENTIFIERS = []

    IDENTIFIER_MAP = {}

    IDENTIFIERS_PROCESSED = False

    @staticmethod
    def processIdentifiers():
        if Law.IDENTIFIERS_PROCESSED:
            return
        Law.LAW_IDENTIFIERS.sort()
        nidentifiers = len( Law.LAW_IDENTIFIERS )
        for i in range( nidentifiers ):
            ident = Law.LAW_IDENTIFIERS[i]
            Law.IDENTIFIER_MAP[ident] = i
        Law.IDENTIFIERS_PROCESSED = True
        print "IDENTIFIER_MAP:"
        print Law.IDENTIFIER_MAP

    @staticmethod
    def clearIdentifiers():
        Law.LAW_IDENTIFIERS = []
        Law.IDENTIFIER_MAP = {}
        Law.IDENTIFIERS_PROCESSED = False

    def __init__(self, chapter, historyMap):
        SectionContainer.__init__(self)
        self.chapter = chapter
        self.article = None
        self.historyMap = historyMap
        self.items = []
        self.name = ''
        self.lawId = None
        self.lawName = None
        self.history = None
        self.parsed = False
        self.childPrefixMap = {}

    def getId(self):
        if not self.parsed:
            self.parse()
        return self.lawId

    def getName(self):
        if not self.parsed:
            self.parse()
        return self.lawName

    def addItem(self, item, headrec=None):
        print "Law.addItem"
        if isinstance(item, HTMLTable):
            print "Law.addItem Adding HTMLTable"
            self.addTable(item)
        else:
            self.addParagraph(item, headrec)

    def addTable(self, item):
        self.items.append(item)

    def addParagraph(self, paragraph, headrec=None):
        if headrec is None:
            m = re.match( ur'(\s*)\((\S+)\)\s+\((\S+)\)\s+.*$', paragraph)
            if m:
                indent = m.group(1)
                par1 = "%s(%s) \n" % (indent, m.group(2))
                indent += " "
                p1 = paragraph.find(m.group(3))
                par2 = "%s%s" % (indent, paragraph[p1-1:])
                print "Law.addParagraph Split paragraph: par1 = %s, par2 = %s" % (par1, par2)
                self.items.append({'content': par1, 'headrec': None})
                self.items.append({'content': par2, 'headrec': None})
                return
            else:
                self.items.append({'content': paragraph, 'headrec': None})
        else:
            print "addParagraph: headrec = ", headrec
            sections = headrec['sections']
            indentcnt = headrec['indent']
            indent = " "
            for i in range(indentcnt):
                indent += " "
            parent = self.childPrefixMap
            for i in range(len(sections)):
                section = sections[i]
                sval = int(section)
                if sval not in parent:
                    par = None
                    if i == (len(sections) - 1):
                        par = "%s(%d) %s\n" % (indent, sval, headrec['sectionName'])
                    else:
                        par = "%s(%d) \n" % (indent, sval)
                    print "addParagraph: appending paragraph: ", par
                    self.items.append({'content': par, 'headrec': None})
                    parent[sval] = {}
                indent += " "
                parent = parent[sval]

    def parseParagraph(self, paragraph):
        m = re.match( ur'(?mu)(\s+)\((\S{1,3})\)\s+(.*)\s*$', paragraph )
        if m:
            return {'indent': countIndent(m.group(1)), 
                    'prefix': m.group(2), 
                    'content': m.group(3)}
        m = re.match( ur'(?mu)(\s*)(.*)\s*$', paragraph )
        return {'indent': countIndent(m.group(1)), 
                'prefix': None, 
                'content': paragraph}
    
    def getHistoryParagraph(self, paragraph):
        if isEditorsNote(paragraph):
            return None
        m = re.match( ur'(?mu)^\s*\((.*\))\s*$', paragraph)
        if not m:
            return None
        hist = m.group(1)
        if ( re.search( r'(?mui)added', hist ) or 
             re.search( r'(?mui)amend', hist ) or 
             re.search( r'(?mui)coun.\s*j', hist ) ):
            return hist
        return None
                      

    def parseItems(self):
        psection = None
        nitems = len(self.items)
        
        print "parseItems: Law = ", self.getId()
        srecs = []
        for i in range(nitems):
            item = self.items[i]
            if not isinstance(item, HTMLTable):
                prec = self.items[i]
                srecs.append(self.parseParagraph(prec['content']))
                print "Paragraph record: ", srecs[-1]
            else:
                srecs.append(None)

        print "parseItems: Law = %s, Starting processing" % (self.getId())
        for i in range(nitems):
            item = self.items[i]
            if isinstance(item, HTMLTable):
                # Note don't set psection here because a table should never be a parent section
                print "Law.parseItems Adding Table"
                section = TableSection(item)
                if psection:
                    psection.addSubsection(section)
                else:
                    self.addSubsection(section)
                continue
            prec = item
            p = prec['content']
            hist = self.getHistoryParagraph(p)
            rec = srecs[i]
            print "parseParagraphs: content = %s, rec = %s" % (p, rec)
            section = Section()
            section.indent = rec['indent']
            section.prefix = rec['prefix']
            section.content = rec['content']
            if hist:
                self.history = hist
                self.addSubsection(section, True)
            elif isEditorsNote(p):
                self.addSubsection(section, True)
            elif psection:
                container = psection.findBestContainer(section)
                if container:
                    container.addSubsection(section)
                else:
                    raise Exception( "Law.parseParagraphs No container found for section")
            else:
                self.addSubsection(section)
            psection = section

                    
    def dump(self):
        content = ""
        for p in self.paragraphs:
            content += p
        print "    Law : %s - %s" % (self.name, content)

    def makeOrderByStructure(self, identifier):
        #orderby = ''
        print "makeOrderByStructure: identifier = ",identifier
        m = re.match( ur'(?iu)Article-(.+)', identifier )
        if m:
            val = convertToOrdinal( m.group(1) )
            return val
        components = re.split( r'-|\.', identifier )
        orderby = ''
        maxcomps = 6
        ncomps = len( components )
        for i in range(maxcomps):
            val = 0
            if i < ncomps:
                comp = components[i]
                val = orderByValue(comp)
            ncomp = '%05d' % (val)
            orderby += ncomp
        return orderby
        #components = re.split( r'-', identifier )
        # for comp in components:
        #     val = int(comp)
        #     ncomp = '%05d' % (val)
        #     orderby += ncomp
        #crec = parseIdentifier( identifier )
        # if len( components ) >= 1:
        #     return orderByValue(components[-1])
        # return orderByValue(identifier)

    def makeOrderByLaw(self, identifier):
        print "makeOrderByLaw: identifier = ", identifier
        orderby = ''
        components = re.split( r'-|\.', identifier )
        for comp in components:
            val = orderByValue(comp)
            ncomp = '%05d' % (val)
            orderby += ncomp
        return orderby

    def writeXML(self, dirname):
        self.parseItems()
        Law.processIdentifiers()
        idval = self.getId()['values']
        ident = self.getId()['identifier']
        idseq = Law.IDENTIFIER_MAP[idval]
        idstr = ""
        comps = self.getId()['components']
        ncomps = len( comps )
        for i in range( ncomps ):
            comp = comps[i]
            idstr += comp
            if i < ( ncomps - 1 ):
                idstr += "-"
        titleId = self.chapter.title.getId()
        secid = titleId + "_" + ident
        filename = os.path.join( dirname, '%s.xml' % ( secid ) )
        print "writeXML: idstr = %s, idval = %s, idseq = %d" % ( idstr, idval, idseq )
        indentStep = "     "
        indent = indentStep
        indents = []
        titleName = self.chapter.title.getName()
        articleId = None
        articleName = None
        if self.article:
            articleId = self.article.getId()
            articleLabel = self.article.getLabel()
            articleName = self.article.getName()
        chapterId = self.chapter.getId()
        chapterName = self.chapter.getName()
        ofile = codecs.open(filename, encoding='utf-8', mode='w')
        ofile.write('<?xml version="1.0" encoding="utf-8"?>\n')
        ofile.write('<law>\n')
        ofile.write('%s<structure>\n' % (indent) )
        indents.append(indent)
        indent += indentStep
        print "writeXML: titleId = %s, chapterId = %s" % (titleId, chapterId)
        ofile.write('%s<unit label="title" identifier="%s" order_by="%s" level="1">%s</unit>\n' % (indent,
                                                                                                   titleId,
                                                                                                   self.makeOrderByStructure(titleId),
                                                                                                   titleName) )

        # if self.article:
        #     ofile.write('%s<unit label="%s" identifier="%s" order_by="%s" level="2">%s</unit>\n' % (indent,
        #                                                                                             articleLabel,
        #                                                                                             articleId,
        #                                                                                             self.makeOrderByStructure(articleId),
        #                                                                                             titleName) )
            

        # ofile.write('%s<unit label="chapter" identifier="%s" order_by="%s" level="2">%s</unit>\n' % (indent,
        #                                                                                              chapterId,
        #                                                                                              self.makeOrderByStructure(chapterId),
        #                                                                                              chapterName) )
        indent = indents.pop()
        ofile.write('%s</structure>\n' % (indent) )
        ofile.write('%s<section_number>%s</section_number>\n' % (indent, secid) )
        ofile.write('%s<catch_line>%s</catch_line>\n' % (indent, self.getName()) )
        ofile.write('%s<order_by>%s</order_by>\n' % (indent, idseq) )
        ofile.write('%s<text>\n' % (indent) )
        indents.append(indent)
        indent += indentStep
        for section in self.subsections:
            section.writeXML(ofile, indent)
        # for p in self.paragraphs:
        #     ofile.write('%s\n' % ( p ) ) 
        indent = indents.pop()
        ofile.write('%s</text>\n' % (indent) )
        history = self.historyMap.get(idval, self.history)
        if history is None:
            history = ""
        ofile.write('%s<history>%s</history>\n' % (indent, history) )
        ofile.write('</law>\n')
        ofile.close()

    def parse(self):
        print "Law.parse name = ",self.name
        #m = re.match( ur'(?um)^(\s*)(?:\u00A7|(?:section))?\s*[^0-9a-zA-Z]?((?:[a-z]+-)?(?:[a-z]+)?(?:\d+-)*\d+)(\.(?:\d+.)*\d+)?\s*(.*)\s*$', self.name )
        m = re.match( PARAGRAPH_PATTERN, self.name )
        #m = re.match( r'(?u)^\s*.?\s*(\d+-\d+-\d+)\s+(.*)\s*$', self.name)
        if m:
            groups = m.groups()
            self.lawId = parseIdentifier( groups[1] )
            Law.LAW_IDENTIFIERS.append( self.lawId['values'] )
            
            # if groups[2]:
            #     self.lawId += groups[2]
            self.lawName = groups[2]
            print "Law.parse id = %s, name = %s" % (self.lawId, self.lawName)
        else:
            raise Exception( "Law.parse Failed to parse name: %s" % (self.name) )
        self.parsed = True

class TableParser(HTMLParser):

    def __init__(self):
        HTMLParser.__init__(self)
        self.state = 'NONE'
        self.substate = 'NONE'
        self.data = ''
        self.trindex = 0
        self.tdcount = 0
        self.section = ''
        self.history = ''
        self.substack = []
        self.historyMap = {}

    def handle_starttag(self, tag, attrs):
        #print "TableParser.handle_starttag state = %s, tag = %s" % (self.state,tag)
        if self.state == 'DONE':
            return
        attrdict = dict(attrs)
        if (self.state == 'NONE') and (tag == 'p'):
            if attrdict.get('class') == 'p3':
                self.state = 'P3'
                self.data = ''
        elif (self.state == 'NONE') and (tag == 'table'):
            #print "TableParser.handle_starttag: table tag, data = ", self.data
            if re.search(r'(?um)CROSS-REFERENCE\s+TABLE', self.data):
                self.state = 'TABLE'
                self.substack.append(self.substate)
                self.substate = 'NONE'
                self.trindex = 0
                self.tdindex = 0
                self.data = ''
        elif (self.state == 'TABLE'):
            #print "TableParser.handle_starttag: TABLE state, substate = %s, tag = %s" % (self.substate, tag)
            if (self.substate == 'NONE') and (tag == 'tr'):
                self.substack.append(self.substate)
                self.substate = 'TR'
                self.trindex += 1
                self.tdindex = 0
            elif (self.substate == 'TR') and (tag == 'td'):
                self.substack.append(self.substate)
                self.substate = 'TD'
                self.tdindex += 1
            elif (self.substate == 'TD') and (tag == 'p'):
                self.substack.append(self.substate)
                self.substate = 'P'

    def handle_endtag(self, tag):
        if self.state == 'DONE':
            return
        if (self.state == 'P3') and (tag == 'p'):
            self.state = 'NONE'
        elif (self.state == 'TABLE') and (tag == 'table'):
            self.state = 'DONE'
        elif (self.state == 'TABLE'):
            if ( ( (self.substate == 'TR') and (tag == 'tr' ) ) or
                 ( (self.substate == 'TD') and (tag =='td') ) or
                 ( (self.substate == 'P') and (tag =='p') ) ):
                if len(self.substack) > 0:
                    self.substate = self.substack.pop()
                else:
                    self.substate = 'NONE'

    def handle_data(self, data):
        if self.state == 'DONE':
            return
        if self.state == 'P3':
            self.data += data
        elif (self.state == 'TABLE') and (self.substate == 'P'):
            #print "TableParser.handle_data, TABLE/P state, tdindex = %d" % (self.tdindex)
            if self.tdindex == 2:
                self.history = data
            elif self.tdindex == 3:
                self.section = data
                self.historyMap[self.section] = self.history

class HTMLItem:

    def __init__(self):
        self.attributes = None
        self.content = ''

class HTMLParagraph(HTMLItem):

    def __init__(self):
        HTMLItem.__init__(self)

class HTMLTable(HTMLItem):

    def __init__(self):
        HTMLItem.__init__(self)
        self.state = 'NONE'
        self.header = None
        self.rows = []
        self.currentRow = None

    def newTableHeader(self):
        #print "HTMLTable: newTableHeader"
        self.state = 'HEADER'

    def newRow(self):
        #print "HTMLTable: newRow"
        if self.state == 'HEADER':
            self.header = self.currentRow
        elif self.state == 'ROW':
            if self.currentRow:
                self.rows.append(self.currentRow)
        self.currentRow = []
        self.state = 'ROW'
        if self.currentRow:
            self.rows.append(self.rows)

    def addCell(self, data):
        #print "HTMLTable: addCell, data = ", data
        self.currentRow.append(data)
        
class TitleParser(HTMLParser):

    CHAPTER_COLOR = "#941100"
    CHAPTER_FONT = "16.0px ArialMT"

    LAW_COLOR = "#011993"
    LAW_FONT = "16.0px 'Times New Roman'"

    LAW_PARAGRAPH_FONT = "12.0px 'Times New Roman'"

    def __init__(self, historyMap, filename):
        HTMLParser.__init__(self)
        self.historyMap = historyMap
        self.waitForTag = None
        self.data = None
        self.state = 'NONE'
        self.tableState = 'NONE'
        self.currentTitle = None
        self.currentItem = None
        self.currentLaw = None
        self.currentCellData = ''
        self.colors = {}
        self.fonts = {}

    def handle_starttag(self, tag, attrs):
        #print "handle_starttag: %s, attrs = %s" % (tag, attrs)
        #if self.waitForTag == 'table':
        #    return
        attrdict = dict(attrs)
        if (tag == 'p') and (self.tableState != 'CELL'):
            self.currentItem = HTMLParagraph()
            self.currentItem.attributes = attrdict
            self.waitForTag = tag
        elif (tag == 'style'):
            self.waitForTag = tag
        elif (tag == 'table'):
            #print 'handle_starttag: tag = table'
            self.currentItem = HTMLTable()
            self.currentItem.attributes = attrdict
            self.waitForTag = tag
        elif tag == 'th':
            self.currentItem.newTableHeader()
        elif tag == 'tr':
            #print 'handle_starttag: tag = tr'
            self.currentItem.newRow()
        elif tag == 'td':
            self.tableState = 'CELL'
            self.waitForTag = 'td'
            self.currentCellData = ''
        elif (tag == 'body') and (self.state == 'NONE'):
            self.state = 'BODY'
            self.waitForTag = tag

    def handle_endtag(self, tag):
        #print "handle_endtag: ", tag
        #if tag != self.waitForTag:
            #return
        if (tag == 'p') and (self.tableState != 'CELL'):
            self.parseItem()
            self.currentItem = None
        elif tag == 'body':
            self.state = 'NONE'
        elif tag =='table':
            #print "TitleParser.handle_endtag tag = table"
            self.parseItem()
            self.currentItem = None
        elif tag == 'td':
            self.currentItem.addCell(self.currentCellData)
            self.tableState = 'NONE'
        self.waitForTag = None

    def handle_data(self, data):
        if self.waitForTag == 'style':
            self.parseStyleData(data)
        elif self.currentItem and isinstance(self.currentItem, HTMLParagraph):
            self.currentItem.content += data
        elif self.tableState == 'CELL':
            self.currentCellData += data

    def parseStyleData(self, data):
        lines = re.split(r'\n', data)
        for line in lines:
            print "parseStyleData: line = ", line
            m = re.match( ur'^\s*p\.(p\d+)\s+{(.*)}$', line)
            if m:
                sclass = m.group( 1 )
                style = m.group( 2 )
                print " sclass = ", sclass
                fields = filter( lambda x: x != '', re.split( r'[;{}]', style ) )
                for field in fields:
                    print "  field = ", field
                    mf = re.match( r'\s*(.*)\s*:\s*(.*)\s*', field )
                    if mf:
                        key = mf.group( 1 )
                        value = mf.group( 2 );
                        print "  key = ", key
                        if key == 'font':
                            self.fonts[sclass] = value
                            print "   font = ", value
                        elif key == 'color':
                            self.colors[sclass] = value
                            print "   color = ", value
                        
            # m = re.match( ur'^\s*p\.(p\d+)\s+{.*font:\s+(.*)[;}].*$', line)
            # if m:
            #     sclass = m.group(1)
            #     font = m.group(2)
            #     print "parseStyleData: sclass = %s, font = %s" % ( sclass, font )
            #     self.fonts[sclass] = font
            # m = re.match( ur'^\s*p\.(p\d+)\s+{.*color:\s*(.*)}\s*$', line)
            # if m:
            #     sclass = m.group(1)
            #     color = m.group(2)
            #     print "parseStyleData: sclass = %s, color = %s" % ( sclass, color )
            #     self.colors[sclass] = color

    def haveChapterTitle(self):
        #print "haveChapterTitle: Start"
        if not isinstance(self.currentItem, HTMLParagraph):
            #print "haveChapterTitle: Returning wrong currentItem"
            return
        p = self.currentItem
        sclass = p.attributes.get('class')
        color = self.colors.get(sclass)
        font = self.fonts.get(sclass)
        if (color == self.CHAPTER_COLOR) and (font == self.CHAPTER_FONT):
            m = re.match(ur'(?uim)^.*chapters.*to.*$', p.content)
            if m:
                return False
            m = re.match(ur'(?uim)^\s*chapter.*$', p.content)
            if not m:
                return False
            return True
        else:
            #print "haveChapterTitle: Returning false"
            return False

    def haveLawTitle(self):
        if not isinstance(self.currentItem, HTMLParagraph):
            return
        p = self.currentItem
        sclass = p.attributes.get('class')
        color = self.colors.get(sclass)
        font = self.fonts.get(sclass)
        if (color == self.LAW_COLOR) and (font == self.LAW_FONT):
            m = re.match(ur'(?uim)^.*sections.*(?:through)|(?:to).*$', p.content)
            if m:
                return False

            return True
        return False

    def haveLawParagraph(self):
        p = self.currentItem
        sclass = p.attributes.get('class')
        font = self.fonts.get(sclass)
        print "haveLawParagraph: sclass = %s, font = %s" % ( sclass, font )
        if font == self.LAW_PARAGRAPH_FONT:
            return True
        
    def parseItem(self):
        if isinstance(self.currentItem, HTMLParagraph):
            self.parseParagraph()
        elif isinstance(self.currentItem, HTMLTable):
            self.parseTable()
        
    def parseParagraph(self):
        if (self.state == 'NONE') or (not self.currentItem):
            return
        p = self.currentItem
        print "parseParagraph: state = %s, content = %s, attrs = %s" % (self.state, p.content.encode('utf-8'), p.attributes)
        if (self.state == 'BODY') and (p.attributes.get('class') == 'p1'):
            self.currentTitle = Title()
            self.currentTitle.content = p.content
            self.currentTitle.addChapter( Chapter( self.currentTitle ) )
            self.state = 'TITLE'
        elif (self.state in ['TITLE', 'CHAPTER', 'ARTICLE', 'LAW']) and self.haveChapterTitle():
            self.currentTitle.addChapter( Chapter(self.currentTitle) )
            self.currentTitle.currentChapter.content = p.content
            print "Adding Chapter, content = ", p.content
            self.state = 'CHAPTER'
        # elif (self.state in ['CHAPTER', 'LAW']) and (p.attributes.get('class') == 'p7'):
        #     #print "TitleParser.parseParagraph Starting article"
        #     self.currentTitle.currentChapter.addArticle( Article(self.currentTitle) )
        #     self.currentTitle.currentChapter.currentArticle.content = p.content
        #     self.state = 'ARTICLE'
        elif ((self.state in ['TITLE', 'ARTICLE', 'CHAPTER', 'LAW']) and 
              self.haveLawTitle() and
              (not isEditorsNote(p.content))):
            print "New law"
            chapter = self.currentTitle.currentChapter
            container = chapter.currentContainer()
            headrec = parseParagraphHeader(p.content)
            law = None

            # if headrec['type'] == 'SECTION':
            #     print "Adding section: chapter = %s" % (chapter.getId())
            #     if not container.currentLaw:
            #         law = Law(chapter, self.historyMap)
            #         law.name = "%s%s %s" % (headrec['indent'], headrec['lawId'], headrec['sectionName'])
            #         container.addLaw(law)
            #     container.currentLaw.addParagraph(p.content, headrec)
            # else:
            #     law = Law(chapter, self.historyMap)
            #     law.name = p.content
            #     print "Adding law, name = ", law.name
            #     if chapter.currentArticle:
            #         law.article = chapter.currentArticle
            #     container.addLaw(law)

            law = Law(chapter, self.historyMap)
            law.name = p.content
            print "Adding law, name = ", law.name
            if chapter.currentArticle:
                law.article = chapter.currentArticle
            container.addLaw(law)
            self.currentLaw = law
            self.currentLaw.parse()

            self.state = 'LAW'
        elif (self.state == 'LAW') and (self.haveLawParagraph() or
                                        (isEditorsNote(p.content))):
            print "Adding law paragraph: ",p.content
            law = self.currentTitle.currentChapter.currentContainer().currentLaw
            law.addItem(p.content)
            self.state = 'LAW'

    def parseTable(self):
        print "TitleParser.parseTable Called"
        if not self.currentLaw:
            print "TitleParser.parseTable: Returning - no current law"
            return
        self.currentLaw.addItem(self.currentItem)


def processFile( filename, historyMap ):
    print "processFile: filename = ", filename
    Law.clearIdentifiers()
    parser = TitleParser( historyMap, filename )
    ifile = file( filename )
    doc = unicode(ifile.read(), 'utf-8')
    ifile.close()
    parser.feed(doc)
    if parser.currentTitle:
        #parser.currentTitle.dump()
        parser.currentTitle.writeXML( 'xml' )

def processDir(dirname, historyMap):
    files = os.listdir(dirname)
    files.sort()
    for fname in files:
        if re.match( ur'article.*\.html', fname ):
            if fname in EXCLUDED_FILES:
                continue
            path = os.path.join(dirname, fname)
            processFile(path, historyMap)

def processTables(dirname):
    path = os.path.join(dirname, "tables.html")
    if not os.path.exists(path):
        return {}
    parser = TableParser()
    ifile = file( path )
    doc = unicode(ifile.read(), 'utf-8')
    ifile.close()
    parser.feed(doc)
    #print "HISTORY_MAP:"
    #print str(parser.historyMap)
    return parser.historyMap
    
if __name__ == "__main__":
    
    os.environ['PYTHONIOENCODING'] = 'UTF-8'

    oparser = OptionParser()

    oparser.add_option("-d",
                       "--directory",
                       dest="directory",
                       help="Directory containing html files")

    oparser.add_option("-r",
                       "--remove-xml",
                       dest="removeXml",
                       action="store_true",
                       default = False,
                       help="Remove XML output directory before parsing")

    oparser.add_option("-i",
                       "--input-file",
                       dest="inputFile",
                       help="Single title html file to parse (optional)")

    (options, args) = oparser.parse_args()

    if not options.directory:
        print "ERROR: Directory must be specified"
        sys.exit( -1 )

    if options.removeXml and os.path.exists( 'xml' ):
        shutil.rmtree( 'xml' )

    if not os.path.exists( "xml" ):
        os.mkdir( 'xml' )

    historyMap = processTables( options.directory )

    if options.inputFile:
        processFile( os.path.join(options.directory, options.inputFile), historyMap )
    else:
        processDir( options.directory, historyMap )
