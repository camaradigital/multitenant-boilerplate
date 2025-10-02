<?php
// Define o nome do tenant de forma segura no início do arquivo.
// Adiciona um valor padrão 'Nossa Equipe' caso o nome não seja encontrado.
$tenantName = \Spatie\Multitenancy\Models\Tenant::current()?->name ?? 'Nossa Equipe';
?>

<?php if (isset($component)) { $__componentOriginalaa758e6a82983efcbf593f765e026bd9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa758e6a82983efcbf593f765e026bd9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => $__env->getContainer()->make(Illuminate\View\Factory::class)->make('mail::message'),'data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mail::message'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

# <?php echo new \Illuminate\Support\EncodedHtmlString($titulo); ?>



Olá!


Você está recebendo uma nova comunicação da nossa equipe. Por favor, leia a mensagem abaixo:


> <?php echo new \Illuminate\Support\EncodedHtmlString($mensagem); ?>



[Acessar o Portal](<?php echo new \Illuminate\Support\EncodedHtmlString(url('/')); ?>)


Agradecemos a sua atenção e estamos à disposição para qualquer esclarecimento.


Atenciosamente,
Equipe <?php echo new \Illuminate\Support\EncodedHtmlString($tenantName); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa758e6a82983efcbf593f765e026bd9)): ?>
<?php $attributes = $__attributesOriginalaa758e6a82983efcbf593f765e026bd9; ?>
<?php unset($__attributesOriginalaa758e6a82983efcbf593f765e026bd9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa758e6a82983efcbf593f765e026bd9)): ?>
<?php $component = $__componentOriginalaa758e6a82983efcbf593f765e026bd9; ?>
<?php unset($__componentOriginalaa758e6a82983efcbf593f765e026bd9); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/emails/tenant/campanha.blade.php ENDPATH**/ ?>