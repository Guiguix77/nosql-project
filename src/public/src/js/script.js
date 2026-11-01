document.addEventListener("DOMContentLoaded", () => {
  const btnTheme = document.getElementById("toggle-theme");
  const btnLang = document.getElementById("toggle-language");
  const LS_LANG = "findbrick_lang", LS_THEME = "findbrick_theme";

  const T = {
    ids: {
      // Global
      "site-title": { fr: "Bienvenue sur FindBrick", en: "Welcome to FindBrick" },
      "nav-home": { fr: "Accueil", en: "Home" },
      "nav-credits": { fr: "Crédits", en: "Credits" },
      "footer-text": { fr: "Crédits : FindBrick ©2026", en: "Credits: FindBrick ©2026" },
      "content": { fr: "Ce site web est réalisé dans le cadre du projet BAWE 2026.", en: "This website is made as part of the BAWE 2026 project." },

      // Index: recherche
      "search-set-title": { fr: "Recherche par numéro de set", en: "Search by set number" },
      "label-setnum": { fr: "Numéro du set :", en: "Set number:" },
      "btn-search-set": { fr: "Rechercher", en: "Search" },
      "hint-set": { fr: "Correspondance exacte sur la colonne avec le numéro de set.", en: "Exact match on the set number column." },
      "search-theme-title": { fr: "Recherche par thème", en: "Search by theme" },
      "label-theme": { fr: "Choisir un thème :", en: "Choose a theme:" },
      "opt-theme-placeholder": { fr: "— sélectionner —", en: "— select —" },
      "or-text": { fr: "ou", en: "or" },
      "btn-search-theme": { fr: "Afficher les sets", en: "Show sets" },
      "hint-theme": { fr: "L’un OU l’autre suffit. Si les deux sont fournis, l’ID est prioritaire.", en: "Either one is enough. If both are provided, ID has priority." },

      // Research: fiche set
      "result-set-title": { fr: "Résultat", en: "Result" },
      "lbl-setnum": { fr: "Set :", en: "Set:" },
      "lbl-year": { fr: "Année :", en: "Year:" },
      "lbl-theme": { fr: "Thème :", en: "Theme:" },
      "lbl-parts": { fr: "Pièces :", en: "Parts:" },
      "view-image": { fr: "Voir l’image", en: "View image" },
      "back-home": { fr: "⟵ Retour", en: "⟵ Back" },
      "back-home-2": { fr: "⟵ Retour", en: "⟵ Back" },
      "no-set": { fr: "Aucun set trouvé pour ce numéro.", en: "No set found for this number." },

      // Research: liste
      "result-theme-title": { fr: "Résultats", en: "Results" },
      "theme-path-label": { fr: "Thème :", en: "Theme:" },
      "count-label": { fr: "Sets trouvés :", en: "Sets found:" },
      "page-label": { fr: "Page", en: "Page" },
      "page-label-2": { fr: "Page", en: "Page" },
      "no-theme-results": { fr: "Aucun set pour ce thème.", en: "No sets for this theme." },
      "back-home-3": { fr: "⟵ Retour", en: "⟵ Back" },
      "back-home-4": { fr: "⟵ Nouvelle recherche", en: "⟵ New search" },

      // Commentaires
      "comments-title": { fr: "Commentaires", en: "Comments" },
      "label-comment": { fr: "Ajouter un commentaire :", en: "Add a comment:" },
      "btn-add-comment": { fr: "Ajouter", en: "Add" },
      "no-comments": { fr: "Aucun commentaire pour ce set.", en: "No comments for this set." },

      // Crédits
      "credits-title": { fr: "Crédits", en: "Credits" },
      "credits-project-title": { fr: "Projet", en: "Project" },
      "credits-project-text": {
        fr: "FindBrick — est un site réalisé dans le cadre du projet NoSQL / BAWE. Objectif : rechercher et afficher des sets LEGO avec persistance hybride (PostgreSQL & Redis).",
        en: "FindBrick — website built for the NoSQL / BAWE project. Goal: search and display LEGO sets with hybrid persistence (PostgreSQL & Redis)."
      },
      "credits-team-title": { fr: "Équipe", en: "Team" },
      "credits-team-text": { fr: "Réalisation : Guillaume MÉRIEUX, Gael LEFEBVRE", en: "Built by: Guillaume MÉRIEUX, Gael LEFEBVRE" },
      "credits-data-title": { fr: "Données & images", en: "Data & Images" },
      "credits-data-text-1": {
        fr: 'Les informations de sets et certaines vignettes proviennent du site <a href="https://rebrickable.com/downloads/" target="_blank">Rebrickable</a>.',
        en: 'Set information and some thumbnails come from <a href="https://rebrickable.com/downloads/" target="_blank">Rebrickable</a>.'
      },
      "credits-data-text-2": {
        fr: '<a href="https://lego.fr" target="_blank">LEGO®</a> est une marque déposée du groupe LEGO, qui ne sponsorise ni n’autorise ce projet.',
        en: '<a href="https://lego.com" target="_blank">LEGO®</a> is a registered trademark of the LEGO Group, which does not sponsor or endorse this project.'
      },
      "credits-data-text-3": {
        fr: 'Les visuels utilisés sur ce site proviennent de sources tierces et restent la propriété de leurs auteurs respectifs.',
        en: 'Visuals used on this site come from third-party sources and remain the property of their respective owners.'
      },
      "credits-tech-title": { fr: "Technologies", en: "Technologies" },
      "credits-tech-text": { fr: "PHP · PostgreSQL · Redis · Docker · HTML/CSS · JavaScript.", en: "PHP · PostgreSQL · Redis · Docker · HTML/CSS · JavaScript." },
      "credits-contact-title": { fr: "Contact", en: "Contact" },
      "credits-contact-text": { fr: "Contact : guillaume.merieux@ensiie.eu", en: "Contact: guillaume.merieux@ensiie.eu" },
      
      // Account preferences
      "preferences-title": { fr: "Préférences", en: "Preferences" },
      "pref-lang-label": { fr: "Langue :", en: "Language:" },
      "pref-theme-label": { fr: "Thème :", en: "Theme:" }
    },
    classes: {
      "i18n-lbl-set": { fr: "Set :", en: "Set:" },
      "i18n-lbl-year": { fr: "Année :", en: "Year:" },
      "i18n-lbl-theme": { fr: "Thème :", en: "Theme:" },
      "i18n-lbl-parts": { fr: "Pièces :", en: "Parts:" },
      "i18n-view-sheet": { fr: "Voir la fiche", en: "View details" },
      "i18n-first-page": { fr: "« Première", en: "« First" },
      "i18n-prev-page": { fr: "‹ Précédente", en: "‹ Previous" },
      "i18n-next-page": { fr: "Suivante ›", en: "Next ›" },
      "i18n-last-page": { fr: "Dernière »", en: "Last »" },
      "i18n-delete-comment": { fr: "Supprimer", en: "Delete" }
    },
    placeholders: {
      "theme_name": { fr: "Recherche texte (partielle)", en: "Text search (partial)" },
      "comment_text": {
        fr: "ex: Acheté en 2026, pièces manquantes...",
        en: "ex: Bought in 2026, missing pieces..."
      }
    }
  };

  function applyLang(lang) {
    for (const id in T.ids) {
      const el = document.getElementById(id); if (!el) continue;
      const txt = T.ids[id][lang]; if (txt == null) continue;
      (txt.includes("<") ? (el.innerHTML = txt) : (el.textContent = txt));
    }
    for (const cls in T.classes) {
      document.querySelectorAll("." + cls).forEach(el => {
        const txt = T.classes[cls][lang]; if (txt) el.textContent = txt;
      });
    }
    for (const id in T.placeholders) {
      const el = document.getElementById(id); if (!el) continue;
      const txt = T.placeholders[id][lang]; if (txt) el.placeholder = txt;
    }
    if (btnLang) btnLang.textContent = lang === "fr" ? "English" : "Français";
  }

  function applyTheme(mode) { document.body.classList.toggle("dark-mode", mode === "dark"); }

  const lang = localStorage.getItem(LS_LANG) || "fr";
  const theme = localStorage.getItem(LS_THEME) || "light";
  applyLang(lang); applyTheme(theme);

  btnLang && btnLang.addEventListener("click", () => {
    const next = (localStorage.getItem(LS_LANG) || "fr") === "fr" ? "en" : "fr";
    localStorage.setItem(LS_LANG, next); applyLang(next);
  });
  btnTheme && btnTheme.addEventListener("click", () => {
    const next = (localStorage.getItem(LS_THEME) || "light") === "light" ? "dark" : "light";
    localStorage.setItem(LS_THEME, next); applyTheme(next);
  });
});
