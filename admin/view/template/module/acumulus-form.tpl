<?php echo $header; ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><!--suppress HtmlUnknownTarget -->
        <a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>
    <?php if ($error_messages) { ?>
    <div class="error">
        <?php if (is_array($error_messages)) { ?>
        <ul>
            <?php foreach($error_messages as $message) { ?>
            <li><?php echo $message; ?></li>
            <?php } ?>
        </ul>
        <?php } else { ?>
        <?php echo $error_messages; ?>
        <?php } ?>
    </div>
    <?php } ?>
    <?php if ($warning_messages) { ?>
    <div class="warning">
        <?php if (is_array($warning_messages)) { ?>
        <ul>
            <?php foreach($warning_messages as $message) { ?>
            <li><?php echo $message; ?></li>
            <?php } ?>
        </ul>
        <?php } else { ?>
        <?php echo $warning_messages; ?>
        <?php } ?>
    </div>
    <?php } ?>
    <?php if ($success_messages) { ?>
    <div class="success">
        <?php if (is_array($success_messages)) { ?>
        <ul>
            <?php foreach($success_messages as $message) { ?>
            <li><?php echo $message; ?></li>
            <?php } ?>
        </ul>
        <?php } else { ?>
        <?php echo $success_messages; ?>
        <?php } ?>
    </div>
    <?php } ?>
    <div class="box">
        <div class="heading">
            <h1><!--suppress HtmlUnknownTarget --><img src="view/image/<?php print $button_icon; ?>" alt=""/> <?php echo $page_title; ?></h1>
            <div class="buttons">
<?php if ($button_save) { ?>                <a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><?php } ?>
<?php if ($button_cancel) { ?>                <a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a><?php } ?>
            </div>
        </div>
        <div class="content"><!--suppress HtmlUnknownTarget -->
            <form action="<?= $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <?php echo $formRenderer->render($form) ?>
            </form>
        </div>
    </div>
    <?php echo $footer; ?>
