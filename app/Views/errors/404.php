<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Page introuvable — PNDM</title>
  <link rel="stylesheet" href="/assets/css/pndm.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--gray-50)">
  <div style="text-align:center;padding:3rem;max-width:500px">
    <div style="font-size:6rem;font-weight:800;color:var(--gray-200);line-height:1">404</div>
    <h1 style="font-size:1.75rem;font-weight:700;color:var(--gray-800);margin:1rem 0 .5rem">Page introuvable</h1>
    <p style="color:var(--gray-500);margin-bottom:2rem"><?= esc($message ?? 'La page que vous cherchez n\'existe pas ou a été déplacée.') ?></p>
    <div style="display:flex;gap:1rem;justify-content:center">
      <a href="/" class="btn btn-primary">Accueil</a>
      <a href="/indicateurs" class="btn btn-ghost">Indicateurs</a>
    </div>
  </div>
</body>
</html>
