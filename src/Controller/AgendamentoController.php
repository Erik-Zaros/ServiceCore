<?php
namespace App\Controller;

use App\Model\Agendamento;

class AgendamentoController
{
    public static function salvar($dados, $posto)
    {
        $ag = new Agendamento($dados, $posto);
        return $ag->salvar();
    }

    public static function listar($posto)
    {
        return Agendamento::listarTodos($posto);
    }
}
