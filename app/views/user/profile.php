<section class="wall">
  <div class="card">
    <h1 class="title">My Profile</h1>
    <p class="subtitle"><?= htmlspecialchars(current_user()['email']) ?></p>

    <form class="form" method="post" action="#">
      <label class="form__label">Full Name
        <input type="text" value="<?= htmlspecialchars(current_user()['name']) ?>" />
      </label>
      <label class="form__label">Email
        <input type="email" value="<?= htmlspecialchars(current_user()['email']) ?>" />
      </label>
      <button class="btn btn--primary btn--xl" type="button">Save</button>
    </form>
  </div>
</section>