<?php
// HTML reutilizable para el chat de paciente y psicologo.

function render_messages_module($user, $other) {
    if (!$other) {
        echo '<p>No hay usuario asignado para conversar.</p>';
        return;
    }

    $messages = messages_between($user['id'], $other['id']);
    ?>
    <div class="messages">
        <?php foreach ($messages as $message): ?>
            <?php $mine = (int) $message['sender_id'] === (int) $user['id'] ? 'mine' : ''; ?>
            <div class="bubble <?= h($mine) ?>">
                <b><?= h($message['sender_name']) ?></b>
                <p><?= h($message['body']) ?></p>
                <?php if ($message['ai_feedback']): ?>
                    <small><?= h($message['ai_feedback']) ?></small>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="post" class="message-form">
        <input type="hidden" name="action" value="send_message">
        <input type="hidden" name="return" value="messages">
        <input type="hidden" name="receiver_id" value="<?= (int) $other['id'] ?>">
        <textarea name="body" required placeholder="Escribe un mensaje"></textarea>
        <button>Enviar</button>
    </form>
    <?php
}
