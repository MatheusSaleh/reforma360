import { iniciarAutocompleteNomenclatura } from './autocomplete-nomenclatura';
import { iniciarClassificacoes } from './classificacoes';
import { iniciarEntradasNumericas } from './entradas-numericas';
import { iniciarSeletorDeMunicipio } from './municipios';
import { iniciarProgressoSimulacao } from './progresso-simulacao';
import { iniciarSeletoresPesquisaveis } from './seletor-pesquisavel';
import { iniciarTooltipsDosCampos } from './tooltips-campos';
import './echo';

iniciarSeletorDeMunicipio();
iniciarSeletoresPesquisaveis();
iniciarTooltipsDosCampos();
iniciarEntradasNumericas();
iniciarAutocompleteNomenclatura();
iniciarClassificacoes();
iniciarProgressoSimulacao();
