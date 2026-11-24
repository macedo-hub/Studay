#!/usr/bin/env python3
"""
IMPORTADOR DE QUESTÕES DO ENEM
Lê o repositório aberto https://github.com/yunger7/enem-api (pasta public/) e gera:
  - dados/enem/<ano>.json            (questões já em HTML, com o gabarito)
  - img/enem/provas/<ano>/<n>/*.webp (imagens das questões, leves)

Como usar (no seu computador, não no servidor):
  git clone --depth 1 https://github.com/yunger7/enem-api
  python3 ferramentas/importar_questoes.py --origem enem-api/public --anos 2021 2022 2023

Precisa do Python 3 e da biblioteca Pillow (pip install pillow).
As questões são das provas oficiais do INEP. Dê o crédito: "Questões: INEP / ENEM".
"""
import argparse, glob, html, json, os, re, sys
from PIL import Image

DIA_POR_AREA = {'linguagens': 1, 'ciencias-humanas': 1, 'ciencias-natureza': 2, 'matematica': 2}


def area_pelo_numero(n):
    """O ENEM sempre segue a mesma ordem: 1-45, 46-90, 91-135 e 136-180.
    (O campo 'discipline' do repositório tem alguns erros, então usamos o número.)"""
    if n <= 45:
        return 'linguagens'
    if n <= 90:
        return 'ciencias-humanas'
    if n <= 135:
        return 'ciencias-natureza'
    return 'matematica'


def md_para_html(texto, ano, pasta, mapa_imagens):
    """Converte o texto da questão (um markdown simples) em HTML seguro."""
    if not texto:
        return ''
    texto = texto.replace('\r', '').replace('\xa0', ' ')

    # imagens primeiro: viram marcadores
    imagens = []
    def guarda_imagem(m):
        url = m.group(1)
        nome = mapa_imagens.get(os.path.basename(url))
        if not nome:
            return ''
        imagens.append('img/enem/provas/%s/%s/%s' % (ano, pasta, nome))
        return '\x00IMG%d\x00' % (len(imagens) - 1)
    texto = re.sub(r'!\[[^\]]*\]\(([^)]*)\)', guarda_imagem, texto)

    # caracteres escapados (\[ \. etc.)
    escapados = []
    def guarda_escape(m):
        escapados.append(m.group(1))
        return '\x01%d\x01' % (len(escapados) - 1)
    texto = re.sub(r'\\([\\`*_{}\[\]()#+\-.!>])', guarda_escape, texto)

    texto = html.escape(texto, quote=False)
    texto = re.sub(r'\*\*(.+?)\*\*', r'<strong>\1</strong>', texto, flags=re.S)
    texto = re.sub(r'(?<![\w*])\*(?!\s)(.+?)(?<!\s)\*(?![\w*])', r'<em>\1</em>', texto)
    texto = re.sub(r'(?<![\w_])_(?!\s)(.+?)(?<!\s)_(?![\w_])', r'<em>\1</em>', texto)

    blocos = []
    for bloco in re.split(r'\n\s*\n', texto.strip()):
        linhas = [l for l in bloco.split('\n')]
        if all(re.match(r'^\s*[-*] ', l) for l in linhas if l.strip()):
            itens = ''.join('<li>%s</li>' % re.sub(r'^\s*[-*] ', '', l).strip() for l in linhas if l.strip())
            blocos.append('<ul>%s</ul>' % itens)
            continue
        bloco = re.sub(r'^#+\s*(.+)$', r'<strong>\1</strong>', bloco, flags=re.M)
        bloco = re.sub(r' {2,}\n', '\n', bloco)
        # Linhas quebradas no meio da frase (quebra do PDF) viram uma linha só.
        # Mantém a quebra em poemas, referências e quando a linha anterior termina em ponto etc.
        bloco = re.sub(r'(?<![.!?:;…»”"\)>])[ \t]*\n(?=[a-zà-ú0-9(,])', ' ', bloco)
        bloco = bloco.strip().replace('\n', '<br>')
        if bloco.startswith('\x00IMG') and bloco.endswith('\x00') and bloco.count('\x00') == 2:
            # imagem sozinha na linha: vira figura (bloco)
            n = int(bloco.strip('\x00')[3:])
            blocos.append('<figure class="sim-figura"><img src="%s" alt="Imagem da questão" loading="lazy"></figure>' % imagens[n])
        else:
            blocos.append('<p>%s</p>' % bloco)
    saida = ''.join(blocos)

    # imagem no meio do texto (símbolos e fórmulas): fica na linha, sem quebrar a frase
    saida = re.sub(r'\x00IMG(\d+)\x00', lambda m: '<img class="sim-inline" src="%s" alt="símbolo ou fórmula" loading="lazy">' % imagens[int(m.group(1))], saida)
    saida = re.sub(r'\x01(\d+)\x01', lambda m: html.escape(escapados[int(m.group(1))], quote=False), saida)
    return saida


def converte_imagem(origem, destino):
    os.makedirs(os.path.dirname(destino), exist_ok=True)
    im = Image.open(origem)
    if im.mode not in ('RGB', 'RGBA'):
        im = im.convert('RGBA' if 'transparency' in im.info else 'RGB')
    if im.width > 1000:
        im = im.resize((1000, round(im.height * 1000 / im.width)), Image.LANCZOS)
    im.save(destino, 'WEBP', quality=80, method=6)


def processa_ano(ano, origem, saida):
    pasta_ano = os.path.join(origem, str(ano), 'questions')
    questoes = []
    for arq in sorted(glob.glob(os.path.join(pasta_ano, '*', 'details.json'))):
        pasta = os.path.basename(os.path.dirname(arq))
        d = json.load(open(arq, encoding='utf-8'))

        # pula questões com dados incompletos (anuladas, sem alternativas ou com imagem quebrada na fonte)
        if 'broken-image' in json.dumps(d):
            print('  pulando %s-%s (imagem quebrada na fonte)' % (ano, pasta))
            continue
        alts = d.get('alternatives') or []
        if len(alts) != 5 or any(not (a.get('text') or a.get('file')) for a in alts) \
                or d.get('correctAlternative') not in [a['letter'] for a in alts]:
            print('  pulando %s-%s (dados incompletos)' % (ano, pasta))
            continue

        # copia e converte as imagens desta questão
        mapa = {}
        for img in glob.glob(os.path.join(os.path.dirname(arq), '*')):
            base = os.path.basename(img)
            if base == 'details.json':
                continue
            novo = os.path.splitext(base)[0] + '.webp'
            mapa[base] = novo
            converte_imagem(img, os.path.join(saida, 'img', 'enem', 'provas', str(ano), pasta, novo))

        alternativas = []
        for a in d['alternatives']:
            img = None
            if a.get('file'):
                nome = mapa.get(os.path.basename(a['file']))
                if nome:
                    img = 'img/enem/provas/%s/%s/%s' % (ano, pasta, nome)
            alternativas.append({'l': a['letter'], 't': md_para_html(a.get('text') or '', ano, pasta, mapa), 'img': img})

        area = area_pelo_numero(d['index'])
        questoes.append({
            'id': '%s-%s' % (ano, pasta),
            'ano': ano,
            'numero': d['index'],
            'area': area,
            'dia': DIA_POR_AREA.get(area, 0),
            'idioma': d.get('language'),
            'enunciado': md_para_html(d.get('context') or '', ano, pasta, mapa),
            'introducao': md_para_html(d.get('alternativesIntroduction') or '', ano, pasta, mapa),
            'alternativas': alternativas,
            'gabarito': d['correctAlternative'],
        })

    questoes.sort(key=lambda q: (q['numero'], q['idioma'] or ''))
    os.makedirs(os.path.join(saida, 'dados', 'enem'), exist_ok=True)
    with open(os.path.join(saida, 'dados', 'enem', '%s.json' % ano), 'w', encoding='utf-8') as f:
        json.dump(questoes, f, ensure_ascii=False, separators=(',', ':'))
    print('ENEM %s: %d questões' % (ano, len(questoes)))


if __name__ == '__main__':
    p = argparse.ArgumentParser()
    p.add_argument('--origem', required=True, help='pasta public/ do repositório enem-api')
    p.add_argument('--anos', nargs='+', type=int, required=True)
    p.add_argument('--saida', default=os.path.join(os.path.dirname(__file__), '..'))
    a = p.parse_args()
    for ano in a.anos:
        processa_ano(ano, a.origem, a.saida)
