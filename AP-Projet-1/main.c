#include <stdio.h>

// Définition de nos booléens
#define VRAI 1
#define FAUX 0

// Définition de la taille du plateau
#define TAILLE 9

// Construction de la structure Case
typedef struct{
    int occupe;
    char couleur;
} Case;

// Construction de la structure Plateau
typedef struct{
    int nbBlanc;
    int nbNoir;
    Case jeu[TAILLE][TAILLE];
} Plateau;


/*********************************************************/
/* Sous-programme creation plateau                       */
/* Locale: plateau - Plateau: Plateau de jeu           */
/* Locale: i - Entier: Indice de boucle                  */
/* Locale: j - Entier: Indice de boucle                  */
/*********************************************************/
Plateau creationPlateau(){
    int i, j;
    Plateau plateau;
    // On remplis les deux premières lignes de pions noirs
    for(i=0; i<2; i++){
        for(j=0; j<TAILLE; j++){
            plateau.jeu[i][j].occupe=VRAI;
            plateau.jeu[i][j].couleur='N';
        }
    }
    // On remplis les lignes centrales de cases vides
    for(i=2; i<TAILLE-2; i++){
        for(j=0; j<TAILLE; j++){
            plateau.jeu[i][j].occupe=FAUX;
            plateau.jeu[i][j].couleur='.';
        }
    }
    // On remplis les deux dernières lignes de pions blancs
    for(i=TAILLE-2; i<TAILLE; i++){
        for(j=0; j<TAILLE; j++){
            plateau.jeu[i][j].occupe=VRAI;
            plateau.jeu[i][j].couleur='B';
        }
    }
    // On initialise les scores
    plateau.nbBlanc = TAILLE*2;
    plateau.nbNoir = TAILLE*2;
    return plateau;
}

/***************************************************************************************/
/* Sous-programme lecture coup                                                         */
/* Donnée: tour: Caractère - switcher permettant de vérifier la couleur qui doit jouer */
/* Résultat: coup - Vecteur[4] d'entiers: coup effectué par l'utilisateur       */
/* Donnée/Résultat: jeu - Matrice[TAILLE][TAILLE] de Cases:  matrice du plateau de jeu */
/* Locale: i,j: Entiers - Indices de boucle                                            */
/* Locale: possible: Entier - Permet de savoir si le coup est possible                 */
/***************************************************************************************/
void lectureCoup(Case jeu[TAILLE][TAILLE], char tour, int coup[4]){
    int possible = FAUX;
    int i, j;
    char saisie_caracteres[5];
    do{
        printf("\nVeuillez saisir un coup : ");
        scanf("%s", saisie_caracteres);
        coup[0] = saisie_caracteres[0] - 'A';
        coup[2] = saisie_caracteres[3] - 'A';
        coup[1] = saisie_caracteres[1] - '1';
        coup[3] = saisie_caracteres[4] - '1';
        // TEST 1 : La case départ contient-elle bien un pion de la couleur dont c'est le tour ?
        if(jeu[coup[0]][coup[1]].couleur == tour){
            // TEST 2 : La case d'arrivé est-elle bien non occupé ?
            if(jeu[coup[2]][coup[3]].occupe == FAUX){
                // TEST 3 : Le déplacement est-il bien rectiligne ? (disjonction du cas horizontal et vertical)
                if(coup[0] == coup[2]){ // CAS HORIZONTAL
                    i = coup[0];
                    // TEST 4 : Dans quel sens de la ligne le déplacement s'effectue et est-il bien supérieur à deux cases ? (disjonction du cas positif et négatif)
                    if(coup[3] - coup[1] > 2){ // CAS POSITIF
                        j = coup[1]+1;
                        while((j < coup[3]) && (jeu[i][j].occupe == FAUX)){
                            j++;
                        }
                        if(j == coup[3]){
                            possible = VRAI;
                        } else{
                            printf("Il y a d'autres pions sur le chemin de votre pion.");
                        }
                    } else if(coup[3] - coup[1] < -2){ // CAS NEGATIF
                        j = coup[3]+1;
                        while((j < coup[1]) && (jeu[i][j].occupe == FAUX)){
                            j++;
                        }
                        if(j == coup[1]){
                            possible = VRAI;
                        } else{
                            printf("Il y a d'autres pions sur le chemin de votre pion.");
                        }
                    } else{ 
                        // Si il est inférieur à deux cases alors soit il est d'une case (aucun problème possible grâce aux précédents test), 
                        // soit il est de deux cases et donc qu'il y ait un pion ou non sur le chemin le déplacement est autorisé.
                        possible = VRAI;
                    }
                } else if(coup[1] == coup[3]){ // CAS VERTICAL
                    j = coup[1];
                    // TEST 4 : Dans quel sens de la ligne le déplacement s'effectue et est-il bien supérieur à deux cases ? (disjonction du cas positif et négatif)
                    if(coup[2] - coup[0] > 2){ // CAS POSITIF
                        i = coup[0]+1;
                        while((i < coup[2]) && (jeu[i][j].occupe == FAUX)){
                            i++;
                        }
                        if(i == coup[2]){
                            possible = VRAI;
                        } else{
                            printf("Il y a d'autres pions sur le chemin de votre pion.");
                        }
                    } else if(coup[2] - coup[0] < -2){ //CAS NEGATIF
                        i = coup[2]+1;
                        while((i < coup[0]) && (jeu[i][j].occupe == FAUX)){
                            i++;
                        }
                        if(i == coup[0]){
                            possible = VRAI;
                        } else{
                            printf("Il y a d'autres pions sur le chemin de votre pion.");
                        }
                    } else{ 
                        // Si il est inférieur à deux cases alors soit il est d'une case (aucun problème possible grâce aux précédents test), 
                        // soit il est de deux cases et donc qu'il y ait un pion ou non sur le chemin le déplacement est autorisé.
                        possible = VRAI;
                    }
                } else{
                    printf("Le déplacement de votre pion n'est pas rectiligne.");
                }
            } else{
                printf("Il y a déjà pion sur la case d'arrivée.");
            }
        } else{
            printf("Ce n'est pas à votre tour de jouer.");
        }
    } while(possible == FAUX);
    // Modification de la case de départ est de celle d'arrivée
    jeu[coup[0]][coup[1]].occupe = FAUX;
    jeu[coup[2]][coup[3]].occupe = VRAI;
    jeu[coup[2]][coup[3]].couleur = tour;
    jeu[coup[0]][coup[1]].couleur = '.';
}

/*********************************************/
/* Sous-programme affichage                  */
/* Donnée: plateau: Plateau - plateau de jeu */
/* Locale: i: Entier - Indice de boucle      */
/* Locale: j: Entier - Indice de boucle      */
/*********************************************/
void affichage(Plateau plateau){
    int i, j;
    // On affiche la première ligne du plateau
    printf("\n  ");
    for(i=1; i<TAILLE+1; i++){
        printf("%d ", i);
    }
    printf("\n");
    // Puis toutes les cases du jeu
    for(i=0; i<TAILLE; i++){
        printf("%c ", 'A'+i); // En commençant par la lettre correspondant à la ligne
        for(j=0; j<TAILLE; j++){
            printf("%c ", plateau.jeu[i][j].couleur); 
        }
        printf("\n");
    }
    // Enfin on affiche les scores actuels
    printf("\nPions Blancs : %d\nPions Noirs : %d\n", plateau.nbBlanc, plateau.nbNoir);
}


/***************************************************************************************/
/* Sous-programme capture                                                              */
/* Donnée: tour: Caractère - switcher permettant de vérifier la couleur qui doit jouer */
/* Donnée: coup - Vecteur[4] d'entiers: coup effectué par l'utilisateur                */ 
/* Donnée/Résultat: plateau: Plateau - Plateau de jeu                                  */
/* Locale: i: Entier - Indice de boucle                                                */
/* Locale: pos: Entier - Position courante d'un pion pour la capture                   */
/***************************************************************************************/
void capture(Plateau *plateau, int coup[4], char tour){
    int i, pos;

    // Parcours de la ligne dans le sens positif
    i = coup[2]+1;
    // Recherche du pion de la même couleur le plus proche dans ce sens si il y en a un
    while((i < TAILLE) && (plateau -> jeu[i][coup[3]].couleur != tour)){
        i += 1;
    }
    if (i < TAILLE){
        pos = i;
        i = coup[2]+1;
        // Vérification qu'il n'y a pas de case vide entre les deux pions
        while((i < pos) && (plateau -> jeu[i][coup[3]].couleur != '.')){
            i += 1;
        }
        // Si il y a une capture alors on vide les cases et on décompte les pions retirés
        if (i == pos){
            for(i=coup[2]+1; i<pos; i++){
                plateau -> jeu[i][coup[3]].couleur = '.';
                plateau -> jeu[i][coup[3]].occupe = FAUX;
            }
            if (tour == 'B'){
                plateau -> nbNoir = plateau -> nbNoir - (pos-coup[2]-1);
            }
            else{
                plateau -> nbBlanc = plateau -> nbBlanc - (pos-coup[2]-1);
            }
        }
    }

    // Parcours de la ligne dans le sens négatif
    i = coup[2]-1;
    // Recherche du pion de la même couleur le plus proche dans ce sens si il y en a un
    while((i > -1) && (plateau -> jeu[i][coup[3]].couleur != tour)){
        i = i-1;
    }
    if (i > -1){
        pos = i;
        i = coup[2]-1;
        // Vérification qu'il n'y a pas de case vide entre les deux pions
        while((i > pos) && (plateau -> jeu[i][coup[3]].couleur != '.')){
            i = i-1;
        }
        // Si il y a une capture alors on vide les cases et on décompte les pions retirés
        if (i == pos){
            for(i=pos+1; i<coup[2]; i++){
                plateau -> jeu[i][coup[3]].couleur = '.';
                plateau -> jeu[i][coup[3]].occupe = FAUX;
            }
            if (tour == 'B'){
                plateau -> nbNoir = plateau -> nbNoir - (coup[2]-pos-1);
            }
            else{
                plateau -> nbBlanc = plateau -> nbBlanc - (coup[2]-pos-1);
            }
        }
    }

    // Parcours de la colonne dans le sens positif
    i = coup[3]+1;
    // Recherche du pion de la même couleur le plus proche dans ce sens si il y en a un
    while((i < TAILLE) && (plateau -> jeu[coup[2]][i].couleur != tour)){
        i += 1;
    }
    if (i < TAILLE){
        pos = i;
        i = coup[3]+1;
        // Vérification qu'il n'y a pas de case vide entre les deux pions
        while((i < pos) && (plateau -> jeu[coup[2]][i].couleur != '.')){
            i += 1;
        }
        // Si il y a une capture alors on vide les cases et on décompte les pions retirés
        if (i == pos){
            for(i=coup[3]+1; i<pos; i++){
                plateau -> jeu[coup[2]][i].couleur = '.';
                plateau -> jeu[coup[2]][i].occupe = FAUX;
            }
            if (tour == 'B'){
                plateau -> nbNoir = plateau -> nbNoir - (pos-coup[3]-1);
            }
            else{
                plateau -> nbBlanc = plateau -> nbBlanc - (pos-coup[3]-1);
            }
        }
    }

    // Parcours de la colonne dans le sens négatif
    i = coup[3]-1;
    // Recherche du pion de la même couleur le plus proche dans ce sens si il y en a un
    while((i > -1) && (plateau -> jeu[coup[2]][i].couleur != tour)){
        i = i-1;
    }
    if (i > -1){
        pos = i;
        i = coup[3]-1;
        // Vérification qu'il n'y a pas de case vide entre les deux pions
        while((i > pos) && (plateau -> jeu[coup[2]][i].couleur != '.')){
            i = i-1;
        }
        // Si il y a une capture alors on vide les cases et on décompte les pions retirés
        if (i == pos){
            for(i=pos+1; i<coup[3]; i++){
                plateau -> jeu[coup[2]][i].couleur = '.';
                plateau -> jeu[coup[2]][i].occupe = FAUX;
            }
            if (tour == 'B'){
                plateau -> nbNoir = plateau -> nbNoir - (coup[3]-pos-1);
            }
            else{
                plateau -> nbBlanc = plateau -> nbBlanc - (coup[3]-pos-1);
            }
        }
    }
}

int main(){
    Plateau plateau = creationPlateau();
    char tour = 'B';
    int coup[4];
    
    // On reste dans la boucle tant que la partie n'est pas terminée (c'est à dire les 2 joueurs ont plus de 5 pions)
    while ((plateau.nbBlanc > 5) && (plateau.nbNoir > 5)){
        // On affiche le plateau
        affichage(plateau);
        // On lit un coup rentré
        lectureCoup(plateau.jeu, tour, coup);
        // On vérifie si il y a capture
        capture(&plateau, coup, tour);

        // On change de tour
        if(tour == 'B')
            tour = 'N';
        else
            tour = 'B';
    }

    //On teste le nombre de pions de chaque couleur pour savoir qui a gagné
    if (plateau.nbNoir < 6){
        printf("\nLe joueur utilisant les pions blancs a gagné la partie\n");
    }
    else{
        printf("\nLe joueur utilisant les pions noirs a gagné la partie\n");
    }

    return 0;
}