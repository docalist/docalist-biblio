<div class="meta">
    <?php if ($author): extract($author[0]) ?>
        <span class="author"><?= $firstname ?> <?= $name ?></span>,
    <?php endif ?>

    <?php if ($type): ?>
        <span class="type"><?= $type ?></span>,
    <?php endif ?>

    <?php if ($organisation): extract($organisation[0]) ?>
        <span class="organisation"><?= implode(', ', $name) ?></span>,
    <?php endif ?>

    <?php if ($journal): ?>
        <span class="journal"><?= $journal ?></span>,
    <?php endif ?>

    <?php if ($volume): ?>
        <span class="volume"><?= $volume ?></span>,
    <?php endif ?>

    <?php if ($issue): ?>
        <span class="issue"><?= $issue ?></span>,
    <?php endif ?>

    <?php if ($date): ?>
        <span class="date"><?= $date ?></span>.
    <?php endif ?>
</div>

<div class="snippet">
    <?php if ($abstract): extract($abstract[0]) ?>
        <div class="abstract"><?= $content ?></div>,
    <?php endif ?>

    <?php if ($topic): extract($topic[0]) ?>
        <div class="topics">Mots-clés : <small><?= implode(', ', $term) ?></small></div>
    <?php endif ?>
</div>