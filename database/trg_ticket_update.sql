CREATE OR REPLACE FUNCTION trg_ticket_update()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = 'FINALIZADO' THEN
        UPDATE tbl_agendamento
        SET status = 'CONCLUIDO'
        WHERE agendamento = NEW.agendamento;

        UPDATE tbl_os
        SET finalizada = TRUE
        WHERE os = NEW.os;
    END IF;

    IF NEW.status = 'CANCELADO' THEN
        UPDATE tbl_agendamento
        SET status = 'CANCELADO'
        WHERE agendamento = NEW.agendamento;

        UPDATE tbl_os
        SET cancelada = TRUE
        WHERE os = NEW.os;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER after_ticket_update
AFTER UPDATE OF status ON tbl_ticket
FOR EACH ROW
WHEN (NEW.status IN ('FINALIZADO', 'CANCELADO'))
EXECUTE FUNCTION trg_ticket_update();
