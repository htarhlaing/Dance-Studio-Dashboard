<?php $__env->startSection('title', 'Login'); ?>

<?php $__env->startSection('content'); ?>
    <div style="max-width: 420px; margin: 40px auto;">
        <h1>Login</h1>
        <div class="muted">Use your email and password.</div>

        <?php if($errors->any()): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 18px;">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo e(url('/login')); ?>">
            <?php echo csrf_field(); ?>
            <div style="margin-top: 14px;">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" required style="width: 100%;">
            </div>

            <div style="margin-top: 14px;">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required style="width: 100%;">
            </div>

            <div style="margin-top: 16px;">
                <button class="btn" type="submit">Login</button>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/auth/login.blade.php ENDPATH**/ ?>