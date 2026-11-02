from pathlib import Path
from zipfile import ZipFile
from lxml import etree
import re, sys
W='{http://schemas.openxmlformats.org/wordprocessingml/2006/main}'
BASE=Path(__file__).resolve().parents[1] / 'resources' / 'templates'
T=BASE / 'generated'
def fetch(f):
 with ZipFile(f) as zipf:root=etree.fromstring(zipf.read('word/document.xml'))
 return [p for p in root.iter(W+'p') if ''.join(x.text or '' for x in p.iter(W+'t')).strip()]
def ptext(p):return ''.join(x.text or '' for x in p.iter(W+'t'))
def regex_fixed(p):
 tokens=[]
 for r in p.iter(W+'r'):
  s=''.join(x.text or '' for x in r.iter(W+'t'))
  if not s:continue
  hi=r.find(W+'rPr/'+W+'highlight')
  if hi is not None and hi.get(W+'val')=='yellow':tokens.append('(?s:.*?)')
  else: tokens.append(re.escape(s))
 pattern=''.join(tokens)
 return pattern
orig=[fetch(BASE/f) for f in ['ST_2_PETUGAS.docx','SPPD_FIKS.docx','BA_FIKS.docx']]
count=0
for n in [1,2]:
 ps=fetch(T/f'paket_{n}_kapal.docx')
 chunks=orig[:2]+[orig[2]]*n
 idx=0
 for section,src in enumerate(chunks):
  for j,a in enumerate(src):
   b=ps[idx];idx+=1
   expected=regex_fixed(a)
   if section >= 2 and 'Banjarmasin, 20 / Oktober / 2026' in ptext(a):
    expected=expected.replace(re.escape('20 / Oktober / 2026'),re.escape('${tanggal_pernyataan_ba}'))
   actual=ptext(b)
   if actual != ptext(a) and not re.fullmatch(expected, actual):
    count+=1
    print('FAIL',n,section,j,'original=',repr(ptext(a)[:160]),'template=',repr(actual[:160]))
 assert idx==len(ps), (idx,len(ps))
print('Uji teks tetap (di luar highlight kuning): ', ('PASS' if count==0 else str(count)+' failures'))
sys.exit(1 if count else 0)
