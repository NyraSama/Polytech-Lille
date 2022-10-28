
struct graphe {
    int *Head;
    int *Succ;
    double *Potent;
};

extern struct graphe* init_graphe(int);

extern void add_graphe(struct graphe*, int);

extern void clear_graphe(struct graphe*);