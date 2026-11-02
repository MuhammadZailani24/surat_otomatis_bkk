"""Optional audit for developers. Needs Python + lxml, not required in XAMPP runtime."""
from pathlib import Path
from zipfile import ZipFile
from lxml import etree
import sys, hashlib
root=Path(__file__).resolve().parents[1]
ns='http://schemas.openxmlformats.org/wordprocessingml/2006/main'
W='{'+ns+'}'
files=[('ST_2_PETUGAS.docx','st_original.docx'),('SPPD_FIKS.docx','sppd_asli.docx'),('BA_FIKS.docx','ba_kapal_1.docx'),('BA_FIKS.docx','ba_kapal_2.docx')]
for original,generated in files:
    with ZipFile(root/'resources/templates'/original) as s,ZipFile(root/'resources/templates/precision'/generated) as t:
        a=etree.fromstring(s.read('word/document.xml'))
        b=etree.fromstring(t.read('word/document.xml'))
        origNodes=list(a.iter(W+'t'));newNodes=list(b.iter(W+'t'))
        assert len(origNodes)==len(newNodes),(generated,'number of runs changed')
        changed=0
        for aa,bb in zip(origNodes,newNodes):
            if (aa.text or '')==(bb.text or ''):continue
            r=aa.getparent()
            while r is not None and r.tag!=W+'r':r=r.getparent()
            y=r is not None and r.find(W+'rPr') is not None and r.find(W+'rPr').find(W+'highlight') is not None and r.find(W+'rPr').find(W+'highlight').get(W+'val')=='yellow'
            parent=aa
            while parent is not None and parent.tag!=W+'p':parent=parent.getparent()
            ps=''.join(x.text or '' for x in parent.iter(W+'t')) if parent is not None else ''
            statement=(original=='BA_FIKS.docx' and 'Banjarmasin' in ps and '20 / Oktober / 2026' in ps)
            permitted=(y or statement or original=='ST_2_PETUGAS.docx' and (aa.text or '').startswith('${'))
            assert permitted,(generated,'non-highlighted text was altered',aa.text,bb.text,ps)
            changed+=1
        original_sect=a.find(W+'body').find(W+'sectPr');new_sect=b.find(W+'body').find(W+'sectPr')
        assert etree.tostring(original_sect)==etree.tostring(new_sect),(generated,'section changed')
        medias=[f for f in s.namelist() if f.startswith('word/media/')]
        assert all(f in t.namelist() and s.read(f)==t.read(f) for f in medias),(generated,'images changed')
        print(f'PASS {generated}: {changed} changed text runs are allowed; sections, page size, margins, image assets unchanged')
